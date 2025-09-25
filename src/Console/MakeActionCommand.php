<?php

namespace MK\Action\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeActionCommand extends Command
{
    protected $signature = 'make:action
        {name : The Action class name (e.g., TestAction)}
        {--domain= : The Domain name (e.g., Design)}
        {--data= : The Data DTO class name (e.g., TestData)}';

    protected $description = 'Generate a new Action class (and optional Data DTO) under App\\Domain\\{Domain}';

    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle(): int
    {
        $name = trim($this->argument('name'));
        $domain = Str::studly(trim((string)$this->option('domain')));
        $data = $this->option('data');

        if ($domain === '') {
            $this->error('The --domain option is required (e.g., --domain=Design).');
            return self::FAILURE;
        }

        $actionClass = Str::studly($name);
        if (! Str::endsWith($actionClass, 'Action')) {
            $actionClass .= 'Action';
        }

        $dataClass = $data ? Str::studly($data) : null;

        // Paths and namespaces
        $basePath = app_path('Domain/' . $domain);
        $actionsPath = $basePath . '/Actions';
        $dataPath = $basePath . '/Data';

        $actionNamespace = 'App\\Domain\\' . $domain . '\\Actions';
        $dataNamespace = 'App\\Domain\\' . $domain . '\\Data';

        // Ensure directories exist
        $this->ensureDirectory($actionsPath);
        if ($dataClass) {
            $this->ensureDirectory($dataPath);
        }

        // Generate Data DTO if requested (and missing)
        if ($dataClass) {
            $dataFile = $dataPath . '/' . $dataClass . '.php';
            if (! $this->files->exists($dataFile)) {
                $this->files->put($dataFile, $this->buildDataStub($dataNamespace, $dataClass));
                $this->info("Created Data: {$dataNamespace}\\{$dataClass}");
            } else {
                $this->line("Data already exists: {$dataNamespace}\\{$dataClass}");
            }
        }

        // Generate Action class
        $actionFile = $actionsPath . '/' . $actionClass . '.php';
        if ($this->files->exists($actionFile)) {
            $this->error("Action already exists: {$actionNamespace}\\{$actionClass}");
            return self::FAILURE;
        }

        $actionNameForStatic = $this->deriveActionName($actionClass);

        $this->files->put(
            $actionFile,
            $this->buildActionStub(
                $actionNamespace,
                $actionClass,
                $dataClass ? ($dataNamespace . '\\' . $dataClass) : null,
                $actionNameForStatic
            )
        );

        $this->info("Created Action: {$actionNamespace}\\{$actionClass}");

        return self::SUCCESS;
    }

    protected function ensureDirectory(string $path): void
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true);
        }
    }

    protected function deriveActionName(string $class): string
    {
        // Convert ClassNameAction -> class-name
        $base = Str::endsWith($class, 'Action') ? Str::substr($class, 0, -6) : $class;
        return Str::kebab(Str::snake($base));
    }

    protected function buildActionStub(string $namespace, string $class, ?string $dataFqn, string $actionName): string
    {
        $stubPath = __DIR__ . '/stubs/action.stub';
        $stub = file_get_contents($stubPath);

        // Only import DTO if provided; Spatie Data is always imported by the stub
        $dataImportStmt = $dataFqn ? ('use ' . $dataFqn . ';') : '';
        $dataShort = $dataFqn ? Str::afterLast($dataFqn, '\\') : 'Data';

        $replacements = [
            '{{ namespace }}' => $namespace,
            '{{ class }}' => $class,
            '{{ data_import_stmt }}' => $dataImportStmt,
            '{{ data_short }}' => $dataShort,
            '{{ action_name }}' => $actionName,
            '{{ hello_message }}' => 'Hello from ' . $class,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $stub);
    }

    protected function buildDataStub(string $namespace, string $class): string
    {
        $stubPath = __DIR__ . '/stubs/data.stub';
        $stub = file_get_contents($stubPath);

        $replacements = [
            '{{ namespace }}' => $namespace,
            '{{ class }}' => $class,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $stub);
    }
}
