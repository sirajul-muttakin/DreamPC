<?php

namespace App\Services;

class CompatibilityEngine
{
    protected SpecExtractorService $specExtractor;

    public function __construct(SpecExtractorService $specExtractor)
    {
        $this->specExtractor = $specExtractor;
    }

    /**
     * Check compatibility of a given array of product IDs representing a system build.
     *
     * @param array $productIds
     * @return array ['is_compatible' => bool, 'incompatibilities' => array]
     */
    public function checkCompatibility(array $productIds): array
    {
        $context = $this->specExtractor->getSystemSpecsContext($productIds);
        $components = $context['components'];

        $isCompatible = true;
        $incompatibilities = [];

        // 1. CPU Socket == Motherboard Socket
        if (isset($components['cpu']) && isset($components['motherboard'])) {
            $cpuSocket = $components['cpu']['specs']['socket'] ?? null;
            $moboSocket = $components['motherboard']['specs']['socket'] ?? null;

            if ($cpuSocket && $moboSocket && strtolower($cpuSocket) !== strtolower($moboSocket)) {
                $isCompatible = false;
                $incompatibilities[] = "CPU Socket ({$cpuSocket}) does not match Motherboard Socket ({$moboSocket}).";
            }
        }

        // 2. RAM Type (DDR4 vs DDR5) == Motherboard RAM Support
        if (isset($components['ram']) && isset($components['motherboard'])) {
            $ramType = $components['ram']['specs']['ram_type'] ?? $components['ram']['specs']['type'] ?? null;
            $moboRamType = $components['motherboard']['specs']['memory_type'] ?? $components['motherboard']['specs']['ram_type'] ?? null;
            
            if ($ramType && $moboRamType && strtolower($ramType) !== strtolower($moboRamType)) {
                $isCompatible = false;
                $incompatibilities[] = "RAM Type ({$ramType}) does not match Motherboard supported RAM ({$moboRamType}).";
            }
        }

        // 3. Motherboard Form Factor fits in Case Form Factor
        if (isset($components['motherboard']) && isset($components['case'])) {
            $moboFormFactor = strtolower($components['motherboard']['specs']['form_factor'] ?? '');
            $caseFormFactors = strtolower($components['case']['specs']['supported_form_factors'] ?? $components['case']['specs']['form_factor'] ?? '');
            
            if ($moboFormFactor && $caseFormFactors) {
                if (!str_contains($caseFormFactors, $moboFormFactor)) {
                    $isCompatible = false;
                    $incompatibilities[] = "Motherboard Form Factor ({$moboFormFactor}) is not supported by Case Form Factors ({$caseFormFactors}).";
                }
            }
        }

        // 4. Total System TDP * 1.25 <= PSU Output Wattage
        $totalTdp = 0;
        foreach ($components as $slug => $component) {
            $tdpStr = $component['specs']['tdp'] ?? null;
            if ($tdpStr) {
                preg_match('/(\d+)/', $tdpStr, $matches);
                if (!empty($matches[1])) {
                    $totalTdp += (int)$matches[1];
                }
            }
        }

        if (isset($components['psu'])) {
            $psuWattageStr = $components['psu']['specs']['wattage'] ?? null;
            if ($psuWattageStr) {
                preg_match('/(\d+)/', $psuWattageStr, $matches);
                if (!empty($matches[1])) {
                    $psuWattage = (int)$matches[1];
                    $requiredWattage = $totalTdp * 1.25;

                    if ($requiredWattage > $psuWattage) {
                        $isCompatible = false;
                        $incompatibilities[] = "PSU Wattage ({$psuWattage}W) is lower than the recommended requirement (" . ceil($requiredWattage) . "W based on system TDP of {$totalTdp}W).";
                    }
                }
            }
        }

        return [
            'is_compatible' => $isCompatible,
            'incompatibilities' => $incompatibilities,
        ];
    }

    /**
     * Detect hardware clearance conflicts and performance bottlenecks for a PC build.
     *
     * @param array $productIds
     * @return array
     */
    public function detectBottlenecksAndConflicts(array $productIds): array
    {
        $context = $this->specExtractor->getSystemSpecsContext($productIds);
        $components = $context['components'];

        $clearanceConflicts = [];
        $bottlenecks = [];

        // 1. GPU Length vs Case Max GPU Clearance
        if (isset($components['gpu']) && isset($components['case'])) {
            $gpuLengthStr = $components['gpu']['specs']['gpu_length'] ?? $components['gpu']['specs']['length'] ?? $components['gpu']['specs']['card_length'] ?? null;
            $caseMaxGpuStr = $components['case']['specs']['max_gpu_clearance'] ?? $components['case']['specs']['max_gpu_length'] ?? $components['case']['specs']['gpu_clearance'] ?? null;

            if ($gpuLengthStr && $caseMaxGpuStr) {
                preg_match('/(\d+)/', $gpuLengthStr, $gpuMatches);
                preg_match('/(\d+)/', $caseMaxGpuStr, $caseMatches);

                if (!empty($gpuMatches[1]) && !empty($caseMatches[1])) {
                    $gpuLength = (int)$gpuMatches[1];
                    $caseMaxGpu = (int)$caseMatches[1];

                    if ($gpuLength > $caseMaxGpu) {
                        $clearanceConflicts[] = [
                            'type' => 'clearance',
                            'title' => 'GPU Length Clearance Conflict',
                            'message' => "The graphics card length ({$gpuLength}mm) exceeds the case's maximum GPU clearance ({$caseMaxGpu}mm).",
                            'recommendation' => "Select a larger PC case or a shorter graphics card to ensure physical fit.",
                        ];
                    }
                }
            }
        }

        // 2. CPU Cooler Height vs Case Max CPU Cooler Height
        $coolerComponent = $components['cooler'] ?? $components['cpu_cooler'] ?? null;
        if ($coolerComponent && isset($components['case'])) {
            $coolerHeightStr = $coolerComponent['specs']['cooler_height'] ?? $coolerComponent['specs']['height'] ?? $coolerComponent['specs']['cpu_cooler_height'] ?? null;
            $caseMaxCoolerStr = $components['case']['specs']['max_cooler_height'] ?? $components['case']['specs']['max_cpu_cooler_height'] ?? $components['case']['specs']['cpu_cooler_clearance'] ?? null;

            if ($coolerHeightStr && $caseMaxCoolerStr) {
                preg_match('/(\d+)/', $coolerHeightStr, $coolerMatches);
                preg_match('/(\d+)/', $caseMaxCoolerStr, $caseCoolerMatches);

                if (!empty($coolerMatches[1]) && !empty($caseCoolerMatches[1])) {
                    $coolerHeight = (int)$coolerMatches[1];
                    $caseMaxCooler = (int)$caseCoolerMatches[1];

                    if ($coolerHeight > $caseMaxCooler) {
                        $clearanceConflicts[] = [
                            'type' => 'clearance',
                            'title' => 'CPU Cooler Height Clearance Conflict',
                            'message' => "The CPU cooler height ({$coolerHeight}mm) exceeds the case's maximum CPU cooler height limit ({$caseMaxCooler}mm).",
                            'recommendation' => "Choose a low-profile CPU cooler or a wider case to allow side panel closing.",
                        ];
                    }
                }
            }
        }

        // 3. Bottleneck: High-End GPU + Entry-Level CPU
        if (isset($components['gpu']) && isset($components['cpu'])) {
            $gpuName = strtolower($components['gpu']['name'] . ' ' . implode(' ', $components['gpu']['specs']));
            $cpuName = strtolower($components['cpu']['name'] . ' ' . implode(' ', $components['cpu']['specs']));

            $isHighEndGpu = (bool)preg_match('/(4080|4090|7900\s*xt|7900\s*xtx|4070\s*ti|3090)/i', $gpuName);
            $isEntryLevelCpu = (bool)preg_match('/(i3|ryzen\s*3|pentium|celeron|athlon)/i', $cpuName);

            if ($isHighEndGpu && $isEntryLevelCpu) {
                $bottlenecks[] = [
                    'type' => 'bottleneck',
                    'title' => 'CPU Bottleneck Warning',
                    'message' => "Pairing a high-performance GPU with an entry-level CPU will result in significant processing bottlenecks during gaming and intensive tasks.",
                    'recommendation' => "Consider upgrading to an Intel Core i5 / Ryzen 5 or higher CPU to get optimal GPU performance.",
                ];
            }
        }

        // 4. Bottleneck: PCIe Gen 4/5 GPU on PCIe Gen 3 Motherboard
        if (isset($components['gpu']) && isset($components['motherboard'])) {
            $gpuPcieStr = strtolower($components['gpu']['specs']['pcie_version'] ?? $components['gpu']['specs']['interface'] ?? $components['gpu']['specs']['bus_interface'] ?? '');
            $moboPcieStr = strtolower($components['motherboard']['specs']['pcie_version'] ?? $components['motherboard']['specs']['pcie_slot_version'] ?? $components['motherboard']['specs']['expansion_slots'] ?? '');

            $isGpuGen4or5 = str_contains($gpuPcieStr, '4.0') || str_contains($gpuPcieStr, '5.0') || str_contains($gpuPcieStr, 'gen 4') || str_contains($gpuPcieStr, 'gen 5');
            $isMoboGen3 = str_contains($moboPcieStr, '3.0') || str_contains($moboPcieStr, 'gen 3');

            if ($isGpuGen4or5 && $isMoboGen3) {
                $bottlenecks[] = [
                    'type' => 'bottleneck',
                    'title' => 'PCIe Bus Interface Bottleneck',
                    'message' => "The graphics card supports PCIe Gen 4/5, but the motherboard is limited to PCIe Gen 3 bandwidth.",
                    'recommendation' => "Upgrade to a motherboard supporting PCIe Gen 4/5 for maximum data transfer rates.",
                ];
            }
        }

        return [
            'clearance_conflicts' => $clearanceConflicts,
            'bottlenecks' => $bottlenecks,
            'all_warnings' => array_merge($clearanceConflicts, $bottlenecks),
        ];
    }
}
