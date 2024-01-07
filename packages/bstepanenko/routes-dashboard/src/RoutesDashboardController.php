<?php

namespace Bstepanenko\RoutesDashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RoutesDashboardController extends Controller
{
    private RouteDashboardService $routeDashboardService;

    public function __construct(RouteDashboardService $routeDashboardService)
    {
        $this->routeDashboardService = $routeDashboardService;
    }

    public function renderDashboardPage(): View
    {
        $routesData = $this->routeDashboardService->getSummaryData();
        $routesFilesList = $this->routeDashboardService->getRoutesFilesNamesList();
        $summaryPercentage = $this->routeDashboardService->calculatePercentageOfSummary($routesData);
        $healthStatusInfo = $this->routeDashboardService->getHealthStatusInfo($summaryPercentage);
        $routesCountInfo = $this->routeDashboardService->getRoutesCountInfo($routesData);
        $generatedFileContent = $this->routeDashboardService->generateFileContent($routesData['otherEndpoints'], true);
        $generatedApiFileContent = $this->routeDashboardService->generateFileContent($routesData['otherEndpoints'], false);

        return view('routes-dashboard::dashboard', [
            'routesCountInfo' => $routesCountInfo,
            'routesFilesList' => $routesFilesList,
            'summaryPercentage' => $summaryPercentage,
            'healthStatusInfo' => $healthStatusInfo,
            'generatedFileContent' => $generatedFileContent,
            'generatedApiFileContent' => $generatedApiFileContent,
            'routesData' => $routesData,
        ]);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $exportType = $request->input('export_type');

        if ($exportType === 'all_routes') {
            return $this->exportAllRoutes($request);
        }

        return $this->exportDefinitionRoutes($request);
    }

    public function exportAllRoutes(Request $request): BinaryFileResponse
    {
        $isView = $request->input('isView') == 'on' ?? false;
        $routesData = $this->routeDashboardService->getSummaryData();
        $allRoutes = $routesData['otherEndpoints']->merge($routesData['apiEndpoints']);
        $fileContent = $this->routeDashboardService->generateFileContent($allRoutes, $isView);

        if ($request->generate) {
            $this->routeDashboardService->fillRouteFile($allRoutes, $isView);
        }

        return $this->fillExportFile($fileContent);
    }

    public function exportDefinitionRoutes(Request $request): BinaryFileResponse
    {
        $isView = $request->input('isView') ?? false;
        $routesData = $this->routeDashboardService->getSummaryData();
        $fileContent = $this->routeDashboardService->generateFileContent($routesData['otherEndpoints'], $isView);

        if ($request->generate) {
            $this->routeDashboardService->fillRouteFile($routesData['otherEndpoints'], $isView);
        }

        return $this->fillExportFile($fileContent);
    }

    private function fillExportFile(string $fileContent): BinaryFileResponse
    {
        $fileName = 'api-generated.php';
        $filePath = storage_path('app/public/' . $fileName);
        file_put_contents($filePath, $fileContent);
        $fileSize = filesize($filePath);
        $headers = [
            'Content-Type' => 'application/php',
            'Content-Disposition' => 'attachment;filename="' . $fileName . '"',
            'Content-Length' => $fileSize,
            'Content-Description' => $fileName,
        ];

        return response()->download($filePath, $fileName, $headers)->deleteFileAfterSend();
    }
}
