<?php

namespace Bstepanenko\RoutesDashboard;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class RouteDashboardService
{
    /**
     * Execute the console command.
     */
    public function getSummaryData(): array
    {
        $prefix = 'api';

        [$apiRoutes, $otherRoutes] = $this->filterRoutesByPrefix(Route::getRoutes(), $prefix);

        $apiRoutesUrls = $this->getRoutesData($apiRoutes);
        $otherRoutesEndpoints = $this->getRoutesData($otherRoutes);
        $trimmedApiEndpoints = $this->trimApiEndpoints($apiRoutesUrls);
        $definitions = $this->findUnusedDefinitions($trimmedApiEndpoints, $otherRoutesEndpoints);
        $summary = $this->getSummary($apiRoutesUrls, $otherRoutesEndpoints, $definitions);
        $routesFilesList = $this->getRoutesFilesNamesList();
        $summaryPercentage = $this->calculatePercentageOfSummary($summary);

        return $summary;
    }
//    public function handle(): void
//    {
//        $output = new ConsoleOutput();
//        $table = $this->createTable($output, [
//            '#',
//            'Status',
//            'Method',
//            'Endpoint',
//            'Comment',
//            'Return Type',
//            'Is View',
//        ]);
//        $tableMissed = $this->createTable($output, [
//            '#',
//            'Status',
//            'Method',
//            'Endpoint',
//        ]);
//
//        $prefix = 'api';
//
//        [$apiRoutes, $otherRoutes] = $this->filterRoutesByPrefix(Route::getRoutes(), $prefix);
//
//        $apiRoutesUrls = $this->getRoutesData($apiRoutes);
//        $otherRoutesEndpoints = $this->getRoutesData($otherRoutes);
//        $trimmedApiEndpoints = $this->trimApiEndpoints($apiRoutesUrls);
//        $definitions = $this->findUnusedDefinitions($trimmedApiEndpoints, $otherRoutesEndpoints);
//        $summary = $this->getSummary($apiRoutesUrls, $otherRoutesEndpoints, $definitions);
//
//        if (count($definitions) > 0) {
//            //            $this->printSummary($output, $summary);
//            //            $this->renderTable($table, 'Difference between routes and API routes list', $definitions);
//            //            $this->renderTable($tableMissed, 'Missed routes list', $definitions->where('status', false));
//            $this->fillRouteFile($otherRoutesEndpoints); // @TODO: define of option - definitions or all routes
//        } else {
//            $output->writeln('<info>Definitions not found</info>');
//        }
//    }

    private function filterRoutesByPrefix(mixed $routes, string $prefix): array
    {
        $filteredApiRoutes = collect($routes)->filter(function ($route) use ($prefix) {
            return str_starts_with($route->uri(), $prefix);
        });

        $filteredOtherRoutes = collect($routes)->filter(function ($route) use ($prefix) {
            return !str_starts_with($route->uri(), $prefix);
        });

        return [
            $filteredApiRoutes,
            $filteredOtherRoutes,
        ];
    }

    private function getRoutesFilesNamesList(): array
    {
        $routeFiles = [];
        $routeDirectory = base_path('routes');

        $files = File::files($routeDirectory);

        foreach ($files as $file) {
            $routeFiles[] = $file->getFilename();
        }

        return $routeFiles;
    }

    private function getRoutesData(mixed $routes): Collection
    {
        return $routes->map(function ($route) {
            $reflectionMethodData = $this->getReflectionMethodData($route);

            return [
                'status' => $reflectionMethodData['status'],
                'method' => $route->methods()[0],
                'url' => url($route->uri()),
                'isView' => $reflectionMethodData['isView'],
                'returnType' => $reflectionMethodData['returnType'],
                'comment' => $reflectionMethodData['comment'],
                'class' => $reflectionMethodData['class'],
                'methodName' => $reflectionMethodData['methodName'] ?? null,
                'controller' => $reflectionMethodData['controller'] ?? null,
                'middlewares' => $reflectionMethodData['middlewares'] ?? null,
            ];
        });
    }

    private function getReflectionMethodData($route): array
    {
        $action = $route->getAction();
        $data = [
            'status' => false,
            'comment' => null,
            'returnType' => null,
            'class' => null,
            'isView' => false,
        ];

        if (array_key_exists('controller', $action) && str_contains($action['controller'], '@')) {
            [$controller, $method] = explode('@', $action['controller']);

            if (method_exists($controller, $method)) {
                $reflection = new \ReflectionMethod($controller, $method);
                $returnType = $reflection->getReturnType();
                $data['prefix'] = $action['prefix'];
                $data['comment'] = $this->trimComment($reflection);
                $data['class'] = $reflection->getDeclaringClass()->getName();
                $data['methodName'] = $method;
                $data['status'] = true;
                $data['middlewares'] = $action['middleware'] ?? [];
                $data['controller'] = $controller;

                if ($returnType) {
                    $data['isView'] = $this->hasViewReturnType($returnType);
                    $data['returnType'] = $this->convertReturnTypesToString($returnType);
                }
            }
        }

        return $data;
    }

    private function trimComment(\ReflectionMethod $reflection): string
    {
        $docComment = $reflection->getDocComment();
        $trimmedComment = preg_replace('/^\/\*\*\s*|\s*\*\/$/m', '', $docComment);
        $trimmedComment = preg_replace('/^\s*\* ?@throws.*$/m', '', $trimmedComment);
        $trimmedComment = preg_replace('/^\s*\* ?@param.*$/m', '', $trimmedComment);
        $trimmedComment = preg_replace('/^\s*\* ?@return.*$/m', '', $trimmedComment);

        return preg_replace('/^\s*\* ?/m', '', $trimmedComment);
    }

    private function hasViewReturnType(mixed $returnType): bool
    {
        if ($returnType instanceof \ReflectionUnionType) {
            foreach ($returnType->getTypes() as $type) {
                if ($this->isViewType($type)) {
                    return true;
                }
            }
        } elseif (
            $returnType instanceof \ReflectionNamedType
            && $this->isViewType($returnType)
        ) {
            return true;
        }

        return false;
    }

    private function isViewType(\ReflectionNamedType $type): bool
    {
        return $type->getName() === 'Illuminate\View\View';
    }

    private function convertReturnTypesToString(mixed $returnType): string
    {
        if ($returnType instanceof \ReflectionUnionType) {
            return implode(', ', array_map(function ($type) {
                return $type->getName();
            }, $returnType->getTypes()));
        }

        return $returnType->getName();
    }

    private function trimApiEndpoints(Collection $apiRoutesUrls): Collection
    {
        return $apiRoutesUrls->map(function ($apiRoutesUrl) {
            return $this->removeApiPrefix($apiRoutesUrl);
        });
    }

    private function removeApiPrefix(array $apiRoutesUrl): array
    {
        $apiRoutesUrl['url'] = str_replace('/api', '', $apiRoutesUrl['url']);

        return $apiRoutesUrl;
    }

    private function findUnusedDefinitions(Collection $trimmedApiEndpoints, Collection $otherRoutesEndpoints): Collection
    {
        return $otherRoutesEndpoints->filter(function ($otherRoute) use ($trimmedApiEndpoints) {
            return !$trimmedApiEndpoints->contains(function ($apiRoute) use ($otherRoute) {
                return $this->areRoutesEqual($otherRoute, $apiRoute);
            });
        });
    }

    private function areRoutesEqual(array $originRoute, array $compareRoute): bool
    {
        return $this->getRouteIdentifier($originRoute) === $this->getRouteIdentifier($compareRoute);
    }

    private function getRouteIdentifier(array $route): string
    {
        return $route['url'] . $route['method'];
    }

    /**
     * Grouping routes.
     */
    private function getGroupAndUngroupRoutesList(array $routesTextList, bool $withSubGroup = false): array
    {
        $groupedRoutes = $this->getGroupRoutes($routesTextList);
        if ($withSubGroup) {
            $subGroupedRoutes = $this->getWithSubgroups($groupedRoutes);
            $separatedSubGroups = $this->separateSubgroupRoutes($subGroupedRoutes);

            return $this->separateGroupAndUngroupRoutes($separatedSubGroups);
        }

        return $this->separateGroupAndUngroupRoutes($groupedRoutes);
    }

    private function getGroupRoutes(array $routes): array
    {
        $grouped = [];
        $pattern = '/Route::\w+\(\'(\/[\w-]+)/';

        foreach ($routes as $route) {
            preg_match($pattern, $route, $matches);
            $uri = $matches[1] ?? null;

            if ($uri) {
                $groupName = explode('/', $uri)[1];
                $trimmedRoute = $this->trimUriAfterGrouping($groupName, $route);
                $grouped[$groupName][] = $trimmedRoute;
            }
        }

        return $grouped;
    }

    private function getWithSubgroups(array $groupedRoutes): array
    {
        $subGrouped = [];
        $patternGroup = '/Route::\w+\(\'(\/[\w-]+)/';
        $patternMiddleware = '/->middleware\((.*?)\)/';

        foreach ($groupedRoutes as $groupName => $routes) {
            foreach ($routes as $route) {
                preg_match($patternGroup, $route, $matches);
                $uri = $matches[1] ?? null;

                preg_match($patternMiddleware, $route, $matches);
                $middlewaresString = $matches[1] ?? null;
                if ($uri) {
                    $subGroupName = explode('/', $uri)[1];
                    $trimmedRoute = $this->trimUriAfterGrouping($subGroupName, $route);
                    $trimmedRoute = $this->trimMiddlewaresAfterGrouping($trimmedRoute);

                    $subGrouped[$groupName][$subGroupName][$middlewaresString][] = $trimmedRoute;
                }
            }
        }

        return $subGrouped;
    }

    private function trimUriAfterGrouping(string $groupName, mixed $route): string
    {
        $trimmedUri = str_replace("/$groupName", '', $route);

        if (str_contains($trimmedUri, "''")) {
            $trimmedUri = str_replace("''", "'/'", $trimmedUri);
        }

        return $trimmedUri;
    }

    private function trimMiddlewaresAfterGrouping(string $route): string
    {
        $pattern = '/->middleware.*$/';

        return preg_replace($pattern, ';', $route);
    }

    private function separateGroupAndUngroupRoutes(array $groupedRoutes): array
    {
        $ungrouped = [];

        foreach ($groupedRoutes as $groupName => $group) {
            foreach ($group as $middleware => $routes) {
                if (
                    count($routes) < 2
                    && isset($routes[0])
                ) {
                    $appendedUri = $this->appendUriAfterUngrouping($groupName, $routes[0]);
                    $appendedMiddlewares = $this->appendMiddlewaresAfterUngrouping($middleware, $appendedUri);
                    $ungrouped[] = $appendedMiddlewares;
                    unset($groupedRoutes[$groupName]);
                }
            }
        }

        return [
            'grouped' => $groupedRoutes,
            'ungrouped' => $ungrouped,
        ];
    }

    private function separateSubgroupRoutes(array $groupedRoutes): array
    {
        foreach ($groupedRoutes as $groupName => $groups) {
            foreach ($groups as $subGroupName => $subGroupWithMiddlewares) {
                foreach ($subGroupWithMiddlewares as $middlewares => $subGroup) {
                    if (
                        count($subGroup) < 2
                        && isset($groupedRoutes[$groupName][$subGroupName][$middlewares])
                    ) {
                        unset($groupedRoutes[$groupName][$subGroupName]);
                        $groupedRoutes[$groupName][$middlewares][] = $this->appendUriAfterUngrouping($subGroupName, $subGroup[0]);
                    }
                }
            }
        }

        return $this->groupByMiddlewares($groupedRoutes);
    }

    private function groupByMiddlewares(array $groupedRoutes): array
    {
        $groupedByMiddlewares = [];

        foreach ($groupedRoutes as $key => $value) {
            if (!isset($groupedByMiddlewares[$key])) {
                $groupedByMiddlewares[$key] = [];
            }
            $groupedByMiddlewares[$key] = array_merge($groupedByMiddlewares[$key], $value);
        }

        return $groupedByMiddlewares;
    }

    private function appendUriAfterUngrouping(string $groupName, string $route): string
    {
        return str_replace("'/'", "'/$groupName'", $route);
    }

    private function appendMiddlewaresAfterUngrouping(string $middlewares, string $route): string
    {
        return str_replace(';', "->middleware({$middlewares});", $route);
    }

    /**
     * Statistics and table.
     */
    private function getSummary(Collection $apiRoutesUrls, Collection $otherRoutesEndpoints, Collection $definitions): array
    {
        return [
            'routes' => Route::getRoutes(),
            'apiEndpoints' => $apiRoutesUrls,
            'otherEndpoints' => $otherRoutesEndpoints,
            'definitions' => $definitions,
        ];
    }

    private function calculatePercentageOfSummary(array $summary): array
    {
        $apiPercentage = (count($summary['apiEndpoints']) / count($summary['routes'])) * 100;
        $otherPercentage = (count($summary['otherEndpoints']) / count($summary['routes'])) * 100;
        $definitionPercentage = (count($summary['definitions']) / count($summary['routes'])) * 100;
        $missedPercentage = (count(collect($summary['definitions'])->where('status', false)) / count($summary['routes'])) * 100;

        return [
            'apiPercentage' => round($apiPercentage, 2),
            'otherPercentage' => round($otherPercentage, 2),
            'definitionPercentage' => round($definitionPercentage, 2),
            'missedPercentage' => round($missedPercentage, 2),
        ];
    }

    private function printSummary(ConsoleOutput $output, array $summary): void
    {
        $output->writeln('<info>Definitions Found</info>');
        $output->writeln('Total routes: ' . count($summary['routes']));
        $output->writeln('API routes: ' . count($summary['apiEndpoints']));
        $output->writeln('Other routes: ' . count($summary['otherEndpoints']));
        $output->writeln('Missed routes: <error>' . count(collect($summary['definitions'])->where('status', false)) . '</error>');
        $output->writeln('Definitions count: <error>' . count($summary['definitions']) . '</error>');
        $output->writeln('');
    }

    private function createTable(ConsoleOutput $output, array $headers): Table
    {
        $table = new Table($output->section());
        $table->setHeaders($headers);

        return $table;
    }

    private function renderTable(Table $table, string $title, Collection $data): void
    {
        $table->setHeaderTitle("$title (" . count($data) . ')');
        $loopStep = 1;
        $tableData = [];

        foreach ($data as $item) {
            $tableData[] = [
                'number' => $loopStep++,
                'status' => $this->getStatusText($item['status']),
                'method' => $item['method'],
                'endpoint' => $item['url'],
                'comment' => $item['comment'],
                'returnType' => $item['returnType'],
                'isView' => $this->getIsViewText($item['isView']),
            ];
        }

        $table->setRows($tableData);
        $table->render();
    }

    private function getStatusText(string $status): string
    {
        return $status ? '<info>Active</info>' : '<error>Missed</error>';
    }

    private function getIsViewText(bool $isView): string
    {
        return $isView ? '<error>Yes</error>' : '';
    }

    /**
     * Generate new route file.
     */
    private function fillRouteFile(Collection $routesList): void
    {
        $routesToTextList = $this->convertApiRoutesToText($routesList, false); // TODO: add option to determine that view display needed
        $groupAndUngroupRoutes = $this->getGroupAndUngroupRoutesList($routesToTextList, true);
        $fileContent = $this->generateRouteFileContent($groupAndUngroupRoutes);

        $filePath = base_path('routes/api-custom.php');

        File::put($filePath, $fileContent);
    }

    private function convertApiRoutesToText(Collection $routesList, bool $isView = false): array
    {
        $routesTextList = [];
        $filteredRoutesForApi = $routesList->where('isView', $isView)
            ->where('status', true)
            ->where('methodName', '!=', null);

        foreach ($filteredRoutesForApi as $route) {
            $urlParts = parse_url($route['url']);
            $query = $urlParts['query'] ?? '';
            $path = $urlParts['path'] . $query;
            $middlewares = $this->getConvertedToTextMiddlewares((array) $route['middlewares']);

            $routesTextList[] = 'Route::' . strtolower($route['method']) . "('{$path}', [{$route['class']}::class, '{$route['methodName']}'])" . $middlewares . ';';
        }

        return $routesTextList;
    }

    private function getConvertedToTextMiddlewares(mixed $middlewares): string
    {
        switch (count($middlewares)) {
            case 0:
                return '';
            case 1:
                return "->middleware('" . $middlewares[0] . "')";
            default:
                $middlewaresNameList = array_values($middlewares);
                $quotedMiddlewares = array_map(function ($middleware) {
                    return "'" . $middleware . "'";
                }, $middlewaresNameList);
                $middlewaresTextList = implode(', ', $quotedMiddlewares);

                return '->middleware([' . $middlewaresTextList . '])';
        }
    }

    private function generateRouteFileContent(array $groupAndUngroupRoutes): string
    {
        $fileContent = "<?php\n\n";
        $fileContent .= "use Illuminate\Support\Facades\Route;\n\n";
        $fileContent .= $this->getGeneratedDescription() . "\n";

        foreach ($groupAndUngroupRoutes['grouped'] as $groupName => $routes) {
            $fileContent .= "Route::prefix('" . $groupName . "')"
                . "\n\t->group(function () {\n";

            foreach ($routes as $subGroupName => $subRoutes) {
                if (is_array($subRoutes)) {
                    foreach ($subRoutes as $middleware => $subRoute) {
                        if ($subGroupName !== $middleware) {
                            if (is_array($subRoute)) {
                                $fileContent .= "\t\tRoute::prefix('" . $subGroupName . "')";
                                $fileContent .= "\n\t\t\t->middleware(" . $middleware . ')';
                                $fileContent .= "\n\t\t\t->group(function () {\n";
                                foreach ($subRoute as $route) {
                                    $fileContent .= "\t\t\t\t" . $route . "\n";
                                }

                                $fileContent .= "\t\t\t});\n\n";
                            } else {
                                $fileContent .= "\t\t" . $subRoute . "\n";
                            }
                        } else {

                        }
                    }
//                    $fileContent .= "\t});\n\n";
                } else {
                    $fileContent .= "\t\t" . $subRoutes . "\n";
                }
            }

            $fileContent .= "\t});\n\n";
        }

        foreach ($groupAndUngroupRoutes['ungrouped'] as $route) {
            $fileContent .= $route . "\n";
        }

        return $fileContent;
    }

    private function getGeneratedDescription(): string
    {
        return
            <<<'EOD'
            /*
            |--------------------------------------------------------------------------
            | Generated API Routes
            |--------------------------------------------------------------------------
            |
            | Here is where you get generated API routes for your application. These
            | routes consist all of your application routes converted to work with API.
            |
            */
            EOD;
    }
}