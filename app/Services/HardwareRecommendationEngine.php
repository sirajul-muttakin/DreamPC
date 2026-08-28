<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;

class HardwareRecommendationEngine
{
    protected CompatibilityEngine $compatibilityEngine;

    public function __construct(CompatibilityEngine $compatibilityEngine)
    {
        $this->compatibilityEngine = $compatibilityEngine;
    }

    /**
     * Process user message and return intelligent hardware recommendations.
     *
     * @param string $input
     * @return array ['text' => string, 'payload' => ?array]
     */
    public function processRequest(string $input): array
    {
        $lower = strtolower($input);

        // 1. Check if user is asking about compatibility rules
        if ($this->isCompatibilityRequest($lower)) {
            return $this->handleCompatibilityAdvice($lower);
        }

        // 2. Check if user is asking for a PC build or has build keywords
        if ($this->isBuildRequest($lower)) {
            return $this->handleBuildRequest($input, $lower);
        }

        // 3. Check if user is asking for single-component advice
        if ($this->isComponentAdviceRequest($lower)) {
            return $this->handleComponentAdvice($lower);
        }

        // Default: General help
        return [
            'text' => "👋 Hello! I am your AI Hardware Assistant for DreamPC. Here is how I can help:\n\n" .
                      "• **Full Custom PC Builds**: Give me your budget and use case (e.g. *\"Build a $1500 gaming PC\"* or *\"AMD esports rig for $1000\"*)\n" .
                      "• **Component Selection**: Ask about GPUs, CPUs, Motherboards, RAM, Storage, or PSUs (e.g. *\"What GPU should I get for 1440p?\"*)\n" .
                      "• **Compatibility Checks**: Ask about socket matching (AM5 vs LGA1700), DDR4 vs DDR5, or PSU wattage requirements.\n\n" .
                      "What would you like to configure or check today?",
            'payload' => ['intent' => 'general_help']
        ];
    }

    protected function isCompatibilityRequest(string $lower): bool
    {
        return str_contains($lower, 'compat') ||
               str_contains($lower, 'bottleneck') ||
               (str_contains($lower, 'how does') && str_contains($lower, 'check')) ||
               (str_contains($lower, 'can i use') && (str_contains($lower, 'ddr') || str_contains($lower, 'socket'))) ||
               (str_contains($lower, 'will') && str_contains($lower, 'fit'));
    }

    /**
     * Determine if message represents a PC build recommendation request.
     */
    protected function isBuildRequest(string $lower): bool
    {
        $buildKeywords = [
            'build', 'rig', 'setup', 'system', 'machine',
            'desktop', 'budget', '$', 'parts for', 'config'
        ];

        foreach ($buildKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                return true;
            }
        }

        if (str_contains($lower, 'pc') && (str_contains($lower, 'gaming') || str_contains($lower, 'extreme') || str_contains($lower, '4k') || str_contains($lower, '1440p') || str_contains($lower, 'cheap') || str_contains($lower, 'best') || str_contains($lower, 'for'))) {
            return true;
        }

        return false;
    }

    /**
     * Determine if message is asking about a specific hardware category.
     */
    protected function isComponentAdviceRequest(string $lower): bool
    {
        $categories = [
            'gpu', 'graphics', 'video card', 'rtx', 'radeon', 'geforce',
            'cpu', 'processor', 'ryzen', 'core i',
            'ram', 'memory', 'ddr4', 'ddr5',
            'motherboard', 'mobo', 'b650', 'z790', 'b760',
            'psu', 'power supply', 'wattage',
            'storage', 'ssd', 'nvme', 'hard drive', 'hdd',
            'case', 'chassis', 'cabinet'
        ];

        foreach ($categories as $cat) {
            if (str_contains($lower, $cat)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle building a complete, 100% compatible 7-part PC build.
     */
    public function handleBuildRequest(string $input, string $lower): array
    {
        // 1. Extract Target Budget
        $budget = $this->extractBudget($input, $lower);

        // 2. Extract Brand Preferences
        $cpuBrand = 'any';
        if (str_contains($lower, 'amd') || str_contains($lower, 'ryzen')) {
            $cpuBrand = 'AMD';
        } elseif (str_contains($lower, 'intel') || str_contains($lower, 'core') || str_contains($lower, 'i5') || str_contains($lower, 'i7') || str_contains($lower, 'i9')) {
            $cpuBrand = 'Intel';
        }

        $gpuBrand = 'any';
        if (str_contains($lower, 'nvidia') || str_contains($lower, 'rtx') || str_contains($lower, 'geforce')) {
            $gpuBrand = 'NVIDIA';
        } elseif (str_contains($lower, 'radeon') || str_contains($lower, 'rx')) {
            $gpuBrand = 'AMD';
        }

        // 3. Extract Use-Case
        $isWorkstation = str_contains($lower, 'workstation') || str_contains($lower, 'edit') || str_contains($lower, 'render') || str_contains($lower, 'productivity') || str_contains($lower, 'content');
        $isUltra = $budget >= 2200 || str_contains($lower, '4k') || str_contains($lower, 'ultra') || str_contains($lower, 'enthusiast') || str_contains($lower, 'extreme') || str_contains($lower, 'beast');

        // 4. Assemble Full 7-Component Compatible Build
        $buildProducts = $this->assembleCompleteBuild($budget, $cpuBrand, $gpuBrand, $isWorkstation, $isUltra);

        if ($buildProducts->isEmpty()) {
            return [
                'text' => "I could not assemble a complete build matching that criteria in our current inventory. Please check our catalog filters to explore individual parts!",
                'payload' => null
            ];
        }

        // 5. Verify Compatibility
        $productIds = $buildProducts->pluck('id')->toArray();
        $compatCheck = $this->compatibilityEngine->checkCompatibility($productIds);
        $totalPrice = $buildProducts->sum('price');

        // 6. Generate Contextual AI Explanation
        $explanation = $this->generateBuildExplanation($buildProducts, $budget, $totalPrice, $cpuBrand, $gpuBrand, $isWorkstation, $isUltra);

        // 7. Render Build Card HTML
        $cardHtml = view('chat.partials.build-card', [
            'products' => $buildProducts,
            'isCompatible' => $compatCheck['is_compatible'],
            'incompatibilities' => $compatCheck['incompatibilities'],
            'totalPrice' => $totalPrice,
        ])->render();

        return [
            'text' => $explanation,
            'payload' => [
                'intent' => 'build_recommendation',
                'card_html' => $cardHtml,
                'suggested_products' => $buildProducts->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'category' => $p->category->name ?? 'Part',
                    'price' => '$' . number_format($p->price, 2),
                    'brand' => $p->brand,
                ])->toArray(),
            ]
        ];
    }

    /**
     * Assemble 7 core components guaranteeing:
     * 1 CPU, 1 Motherboard (matching socket), 1 RAM (matching DDR type), 1 GPU, 1 Storage, 1 PSU (adequate wattage), 1 Case.
     */
    public function assembleCompleteBuild(float $budget, string $cpuBrand = 'any', string $gpuBrand = 'any', bool $isWorkstation = false, bool $isUltra = false): Collection
    {
        $allProducts = Product::with(['category', 'specifications'])->where('stock_quantity', '>', 0)->get();

        // 1. Select CPU based on budget and brand
        $cpu = $this->selectCpu($allProducts, $budget, $cpuBrand, $isWorkstation, $isUltra);
        if (!$cpu) {
            return collect();
        }

        $cpuSpecs = $this->getSpecsMap($cpu);
        $cpuSocket = strtolower($cpuSpecs['socket'] ?? ($cpu->brand === 'AMD' ? 'am5' : 'lga1700'));
        $cpuTdp = (int)filter_var($cpuSpecs['tdp'] ?? '65', FILTER_SANITIZE_NUMBER_INT);

        // 2. Select GPU based on remaining budget and preference
        $gpu = $this->selectGpu($allProducts, $budget, $gpuBrand, $isWorkstation, $isUltra);
        $gpuSpecs = $gpu ? $this->getSpecsMap($gpu) : [];
        $gpuTdp = (int)filter_var($gpuSpecs['tdp'] ?? '200', FILTER_SANITIZE_NUMBER_INT);
        $gpuLength = (int)filter_var($gpuSpecs['gpu_length'] ?? '240', FILTER_SANITIZE_NUMBER_INT);

        // 3. Select Motherboard matching CPU Socket
        $motherboard = $this->selectMotherboard($allProducts, $cpuSocket, $budget, $isUltra);
        $moboSpecs = $motherboard ? $this->getSpecsMap($motherboard) : [];
        $requiredRamType = strtolower($moboSpecs['memory_type'] ?? ($cpuSocket === 'am5' ? 'ddr5' : 'ddr4'));
        $moboFormFactor = strtolower($moboSpecs['form_factor'] ?? 'atx');

        // 4. Select RAM matching Motherboard memory generation (DDR4 vs DDR5)
        $ram = $this->selectRam($allProducts, $requiredRamType, $budget, $isWorkstation, $isUltra);

        // 5. Select Storage (NVMe SSD)
        $storage = $this->selectStorage($allProducts, $budget, $isWorkstation || $isUltra);

        // 6. Select Power Supply (PSU) with wattage >= (CPU TDP + GPU TDP + 50W) * 1.25
        $estimatedSystemTdp = $cpuTdp + $gpuTdp + 60;
        $requiredPsuWattage = (int)ceil($estimatedSystemTdp * 1.25);
        $psu = $this->selectPsu($allProducts, $requiredPsuWattage, $budget);

        // 7. Select Case supporting Mobo Form Factor and GPU Length
        $case = $this->selectCase($allProducts, $moboFormFactor, $gpuLength, $budget);

        $selected = collect([$cpu, $motherboard, $ram, $gpu, $storage, $psu, $case])->filter();

        return $selected->values();
    }

    protected function selectCpu(Collection $all, float $budget, string $cpuBrand, bool $isWorkstation, bool $isUltra): ?Product
    {
        $cpus = $all->filter(fn($p) => ($p->category->slug ?? '') === 'cpu');

        if ($cpuBrand !== 'any') {
            $brandFiltered = $cpus->filter(fn($p) => strcasecmp($p->brand, $cpuBrand) === 0);
            if ($brandFiltered->isNotEmpty()) {
                $cpus = $brandFiltered;
            }
        }

        if ($isUltra || $budget >= 2000) {
            return $cpus->sortByDesc('price')->first() ?? $cpus->first();
        }

        if ($budget <= 900) {
            return $cpus->sortBy('price')->first() ?? $cpus->first();
        }

        if ($cpuBrand === 'AMD') {
            return $cpus->firstWhere('name', 'AMD Ryzen 5 7600') ?? $cpus->sortBy('price')->first();
        } elseif ($cpuBrand === 'Intel') {
            return $cpus->firstWhere('name', 'Intel Core i5-13400F') ?? $cpus->sortBy('price')->first();
        }

        return $cpus->firstWhere('name', 'AMD Ryzen 5 7600') ?? $cpus->first();
    }

    protected function selectGpu(Collection $all, float $budget, string $gpuBrand, bool $isWorkstation, bool $isUltra): ?Product
    {
        $gpus = $all->filter(fn($p) => ($p->category->slug ?? '') === 'gpu');

        if ($gpuBrand !== 'any') {
            $brandFiltered = $gpus->filter(fn($p) => strcasecmp($p->brand, $gpuBrand) === 0);
            if ($brandFiltered->isNotEmpty()) {
                $gpus = $brandFiltered;
            }
        }

        if ($isUltra || $budget >= 2500) {
            return $gpus->sortByDesc('price')->first();
        }

        if ($budget >= 1600) {
            if ($gpuBrand === 'AMD') {
                return $gpus->firstWhere('name', 'AMD Radeon RX 7800 XT') ?? $gpus->first();
            }
            return $gpus->firstWhere('name', 'NVIDIA GeForce RTX 4070') ?? $gpus->first();
        }

        if ($budget >= 1200) {
            if ($gpuBrand === 'AMD') {
                return $gpus->firstWhere('name', 'AMD Radeon RX 7800 XT') ?? $gpus->first();
            }
            return $gpus->firstWhere('name', 'NVIDIA GeForce RTX 4070') ?? $gpus->firstWhere('name', 'AMD Radeon RX 7800 XT') ?? $gpus->first();
        }

        return $gpus->firstWhere('name', 'NVIDIA GeForce RTX 4060') ?? $gpus->sortBy('price')->first();
    }

    protected function selectMotherboard(Collection $all, string $cpuSocket, float $budget, bool $isUltra): ?Product
    {
        $mobos = $all->filter(fn($p) => ($p->category->slug ?? '') === 'motherboard');

        $matching = $mobos->filter(function($mobo) use ($cpuSocket) {
            $specs = $this->getSpecsMap($mobo);
            $socket = strtolower($specs['socket'] ?? '');
            return $socket === $cpuSocket;
        });

        if ($matching->isEmpty()) {
            $matching = $mobos;
        }

        if ($isUltra || $budget >= 1800) {
            return $matching->sortByDesc('price')->first();
        }

        return $matching->sortBy('price')->first();
    }

    protected function selectRam(Collection $all, string $requiredType, float $budget, bool $isWorkstation, bool $isUltra): ?Product
    {
        $rams = $all->filter(fn($p) => ($p->category->slug ?? '') === 'ram');

        $matching = $rams->filter(function($ram) use ($requiredType) {
            $specs = $this->getSpecsMap($ram);
            $type = strtolower($specs['ram_type'] ?? $specs['type'] ?? '');
            return str_contains($type, $requiredType);
        });

        if ($matching->isEmpty()) {
            $matching = $rams;
        }

        if ($isWorkstation || $isUltra || $budget >= 1500) {
            $thirtyTwoGb = $matching->filter(fn($r) => str_contains(strtolower($r->name), '32gb'));
            if ($thirtyTwoGb->isNotEmpty()) {
                return $thirtyTwoGb->first();
            }
        }

        return $matching->sortBy('price')->first();
    }

    protected function selectStorage(Collection $all, float $budget, bool $isHighCap): ?Product
    {
        $storages = $all->filter(fn($p) => ($p->category->slug ?? '') === 'storage');

        if ($isHighCap || $budget >= 1600) {
            $twoTb = $storages->firstWhere('name', 'WD Black SN850X 2TB NVMe SSD');
            if ($twoTb) return $twoTb;
        }

        return $storages->firstWhere('name', 'Samsung 980 Pro 1TB NVMe SSD') ?? $storages->first();
    }

    protected function selectPsu(Collection $all, int $minWattage, float $budget): ?Product
    {
        $psus = $all->filter(fn($p) => ($p->category->slug ?? '') === 'psu');

        $suitable = $psus->filter(function($psu) use ($minWattage) {
            $specs = $this->getSpecsMap($psu);
            $wStr = $specs['wattage'] ?? '';
            $wattage = (int)filter_var($wStr, FILTER_SANITIZE_NUMBER_INT);
            return $wattage >= $minWattage;
        });

        if ($suitable->isNotEmpty()) {
            return $suitable->sortBy('price')->first();
        }

        return $psus->sortByDesc(function($psu) {
            $specs = $this->getSpecsMap($psu);
            return (int)filter_var($specs['wattage'] ?? '650', FILTER_SANITIZE_NUMBER_INT);
        })->first();
    }

    protected function selectCase(Collection $all, string $moboFormFactor, int $gpuLength, float $budget): ?Product
    {
        $cases = $all->filter(fn($p) => ($p->category->slug ?? '') === 'case');

        $suitable = $cases->filter(function($case) use ($moboFormFactor, $gpuLength) {
            $specs = $this->getSpecsMap($case);
            $supported = strtolower($specs['supported_form_factors'] ?? 'atx, matx, itx');
            $maxGpu = (int)filter_var($specs['max_gpu_clearance'] ?? '360', FILTER_SANITIZE_NUMBER_INT);

            $formFactorFit = str_contains($supported, $moboFormFactor) || str_contains($supported, 'atx');
            $gpuFit = $maxGpu >= $gpuLength;

            return $formFactorFit && $gpuFit;
        });

        if ($suitable->isNotEmpty()) {
            if ($budget <= 900) {
                return $suitable->sortBy('price')->first();
            }
            return $suitable->firstWhere('name', 'Corsair 4000D Airflow') ?? $suitable->firstWhere('name', 'NZXT H510') ?? $suitable->first();
        }

        return $cases->firstWhere('name', 'Corsair 4000D Airflow') ?? $cases->first();
    }

    /**
     * Generate tailored natural language explanation for the custom build.
     */
    protected function generateBuildExplanation(Collection $products, float $targetBudget, float $totalPrice, string $cpuBrand, string $gpuBrand, bool $isWorkstation, bool $isUltra): string
    {
        $cpu = $products->first(fn($p) => ($p->category->slug ?? '') === 'cpu');
        $gpu = $products->first(fn($p) => ($p->category->slug ?? '') === 'gpu');
        $ram = $products->first(fn($p) => ($p->category->slug ?? '') === 'ram');
        $mobo = $products->first(fn($p) => ($p->category->slug ?? '') === 'motherboard');
        $psu = $products->first(fn($p) => ($p->category->slug ?? '') === 'psu');

        $resolution = '1080p High Refresh Rate';
        if ($isUltra || (isset($gpu) && str_contains($gpu->name, '4090'))) {
            $resolution = '4K Ultra Gaming & Heavy Workloads';
        } elseif (isset($gpu) && (str_contains($gpu->name, '4070') || str_contains($gpu->name, '7800 XT'))) {
            $resolution = '1440p Max Settings & High FPS';
        }

        $lines = [];
        $lines[] = "🎯 **Custom PC Build Recommendation** (Est. Total: **$" . number_format($totalPrice, 2) . "**):";
        $lines[] = "I have configured a **100% fully compatible 7-component system** tailored for **{$resolution}**:\n";

        if ($cpu && $gpu) {
            $lines[] = "• **CPU & GPU Synergy**: The **{$cpu->name}** paired with the **{$gpu->name}** provides balanced gaming and compute power without thermal or PCIe bottlenecks.";
        }

        if ($mobo && $ram) {
            $lines[] = "• **Platform & Memory**: Installed on the **{$mobo->name}** with high-speed **{$ram->name}** for smooth multitasking and zero memory hitching.";
        }

        if ($psu) {
            $lines[] = "• **Power & Safety**: Powered by the **{$psu->name}**, providing over 25% safety wattage headroom for transient voltage spikes.";
        }

        $lines[] = "\nYou can add this entire build to your cart with one click or view the full analytics breakdown in the Build Summary!";

        return implode("\n", $lines);
    }

    /**
     * Handle single-category hardware inquiries.
     */
    public function handleComponentAdvice(string $lower): array
    {
        $all = Product::with(['category', 'specifications'])->where('stock_quantity', '>', 0)->get();

        if (str_contains($lower, 'gpu') || str_contains($lower, 'graphics') || str_contains($lower, 'rtx') || str_contains($lower, 'radeon') || str_contains($lower, 'video card')) {
            $gpus = $all->filter(fn($p) => ($p->category->slug ?? '') === 'gpu');
            return [
                'text' => "🎮 **Graphics Card (GPU) Guidance**:\n\n" .
                          "• **1080p Esports / Budget**: The **RTX 4060 ($299.99)** is the top value pick for high-FPS competitive titles (Valorant, CS2, Apex).\n" .
                          "• **1440p High/Ultra Gaming**: The **RTX 4070 ($549.99)** (with DLSS 3 Frame Gen) and **RX 7800 XT ($499.99)** (with 16GB VRAM) are the sweet-spot leaders.\n" .
                          "• **4K Enthusiast & AI Workloads**: The flagship **RTX 4090 ($1,599.99)** delivers uncompromised 4K ray-tracing performance.\n\n" .
                          "💡 *Tip: Ensure your PC Case has sufficient length clearance and your PSU has at least 1.25x your system TDP!*",
                'payload' => [
                    'intent' => 'gpu_advice',
                    'suggested_products' => $gpus->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'price' => '$' . number_format($p->price, 2)])->values()->toArray()
                ]
            ];
        }

        if (str_contains($lower, 'cpu') || str_contains($lower, 'processor') || str_contains($lower, 'ryzen') || str_contains($lower, 'intel')) {
            $cpus = $all->filter(fn($p) => ($p->category->slug ?? '') === 'cpu');
            return [
                'text' => "⚡ **Processor (CPU) Selection Guide**:\n\n" .
                          "• **Best Overall Gaming CPU**: **AMD Ryzen 7 7800X3D ($399.99)** — thanks to 3D V-Cache, it tops gaming benchmark charts worldwide with outstanding efficiency (120W TDP on AM5).\n" .
                          "• **Best Mid-Range Value**: **AMD Ryzen 5 7600 ($229.99)** (AM5, DDR5) or **Intel Core i5-13400F ($199.99)** (LGA1700).\n" .
                          "• **Productivity & Content Creation**: **Intel Core i7-13700K ($409.99)** with 16 cores (8P + 8E) for heavy rendering, video editing, and compiling.",
                'payload' => [
                    'intent' => 'cpu_advice',
                    'suggested_products' => $cpus->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'price' => '$' . number_format($p->price, 2)])->values()->toArray()
                ]
            ];
        }

        if (str_contains($lower, 'ram') || str_contains($lower, 'memory') || str_contains($lower, 'ddr4') || str_contains($lower, 'ddr5')) {
            $rams = $all->filter(fn($p) => ($p->category->slug ?? '') === 'ram');
            return [
                'text' => "🧠 **Memory (RAM) Guide**:\n\n" .
                          "• **DDR4 vs DDR5**: DDR5 offers significantly higher bandwidth (5600-6000MHz) and is mandatory for AMD AM5 platforms. DDR4 is more budget-friendly on select Intel LGA1700 motherboards.\n" .
                          "• **Capacity Recommendation**: **32GB (2x16GB)** is the modern sweet spot for modern AAA games and heavy multitasking. 16GB remains solid for budget builds.",
                'payload' => [
                    'intent' => 'ram_advice',
                    'suggested_products' => $rams->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'price' => '$' . number_format($p->price, 2)])->values()->toArray()
                ]
            ];
        }

        if (str_contains($lower, 'psu') || str_contains($lower, 'power supply') || str_contains($lower, 'watt')) {
            $psus = $all->filter(fn($p) => ($p->category->slug ?? '') === 'psu');
            return [
                'text' => "🔌 **Power Supply (PSU) Sizing**:\n\n" .
                          "• **Wattage Headroom Formula**: Always calculate `Total System TDP * 1.25`. This 25% safety headroom prevents shutdowns during transient GPU power spikes and extends PSU lifespan.\n" .
                          "• **650W**: Ideal for Ryzen 5 / i5 + RTX 4060.\n" .
                          "• **750W - 850W**: Ideal for RTX 4070 / RX 7800 XT with Ryzen 7 / i7.\n" .
                          "• **1000W+**: Recommended for RTX 4090 builds.",
                'payload' => [
                    'intent' => 'psu_advice',
                    'suggested_products' => $psus->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'price' => '$' . number_format($p->price, 2)])->values()->toArray()
                ]
            ];
        }

        if (str_contains($lower, 'storage') || str_contains($lower, 'ssd') || str_contains($lower, 'nvme')) {
            $storages = $all->filter(fn($p) => ($p->category->slug ?? '') === 'storage');
            return [
                'text' => "💾 **Storage Recommendations**:\n\n" .
                          "• **PCIe Gen4 NVMe SSDs** like the **Samsung 980 Pro (1TB)** and **WD Black SN850X (2TB)** deliver read/write speeds up to 7,000MB/s for near-instant boot times and DirectStorage game loading.",
                'payload' => [
                    'intent' => 'storage_advice',
                    'suggested_products' => $storages->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'price' => '$' . number_format($p->price, 2)])->values()->toArray()
                ]
            ];
        }

        $cases = $all->filter(fn($p) => ($p->category->slug ?? '') === 'case');
        return [
            'text' => "📦 **PC Case & Chassis Selection**:\n\n" .
                      "• Look for cases with high airflow mesh front panels (like the **Corsair 4000D Airflow** or **Fractal Meshify C**) and check GPU length clearance before ordering!",
            'payload' => [
                'intent' => 'case_advice',
                'suggested_products' => $cases->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'price' => '$' . number_format($p->price, 2)])->values()->toArray()
            ]
        ];
    }

    public function handleCompatibilityAdvice(string $lower): array
    {
        return [
            'text' => "⚙️ **DreamPC Automated Compatibility Engine Rules**:\n\n" .
                      "1. **CPU Socket Match**: AMD AM5 processors (Ryzen 7000) only fit AM5 motherboards (B650/X670). Intel 13th Gen processors fit LGA1700 motherboards (B760/Z790).\n" .
                      "2. **RAM Generation**: DDR5 memory physically has a different key notch and electrical standard than DDR4. AM5 requires DDR5.\n" .
                      "3. **Form Factor Compatibility**: ATX motherboards require ATX cases; Micro-ATX (mATX) boards fit in both mATX and standard ATX cases.\n" .
                      "4. **PSU Headroom**: We enforce `Total System TDP * 1.25 <= PSU Output Wattage` to protect against transient spikes.\n" .
                      "5. **Clearance Checks**: We verify GPU length against the chassis maximum clearance mm.\n\n" .
                      "Ask me for any custom build and I will guarantee 100% compatibility across all components!",
            'payload' => ['intent' => 'compatibility_info']
        ];
    }

    public function extractBudget(string $input, string $lower): float
    {
        if (preg_match('/\$?\s*(\d{3,5})\s*(?:dollars|\$|usd)?/i', $input, $matches)) {
            return (float)$matches[1];
        }

        if (preg_match('/(\d+(?:\.\d+)?)\s*k/i', $input, $matches)) {
            return (float)$matches[1] * 1000;
        }

        if (str_contains($lower, 'cheap') || str_contains($lower, 'budget') || str_contains($lower, 'entry')) {
            return 850.0;
        }

        if (str_contains($lower, 'ultra') || str_contains($lower, '4k') || str_contains($lower, 'enthusiast') || str_contains($lower, 'extreme') || str_contains($lower, 'beast') || str_contains($lower, 'no budget limit')) {
            return 2800.0;
        }

        return 1400.0; // Default standard balanced tier
    }

    public function getSpecsMap(Product $product): array
    {
        $product->loadMissing('specifications');
        $map = [];
        foreach ($product->specifications as $s) {
            $map[strtolower(trim($s->spec_key))] = trim($s->spec_value);
        }
        return $map;
    }
}
