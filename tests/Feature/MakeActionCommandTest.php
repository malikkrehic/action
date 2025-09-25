<?php

namespace MK\Action\Tests\Feature;

use Illuminate\Filesystem\Filesystem;
use function Pest\Laravel\artisan;

it('generates action and data in the given domain', function () {
    // Run generator
    artisan('make:action', [
        'name' => 'TestAction',
        '--domain' => 'Design',
        '--data' => 'TestData',
    ])->assertSuccessful();

    $actionPath = base_path('app/Domain/Design/Actions/TestAction.php');
    $dataPath = base_path('app/Domain/Design/Data/TestData.php');

    expect($actionPath)->toBeFile();
    expect($dataPath)->toBeFile();

    $actionContents = file_get_contents($actionPath);
    $dataContents = file_get_contents($dataPath);

    expect($actionContents)
        ->toContain('namespace App\\Domain\\Design\\Actions;')
        ->toContain('class TestAction extends BaseAction')
        ->toContain("return 'test';")
        ->toContain('public static function getDataType(): string')
        ->toContain('return TestData::class;');

    expect($dataContents)
        ->toContain('namespace App\\Domain\\Design\\Data;')
        ->toContain('class TestData extends Data');
});

it('generates action without data and falls back to Spatie Data', function () {
    // Run generator without --data
    artisan('make:action', [
        'name' => 'SimpleAction',
        '--domain' => 'Design',
    ])->assertSuccessful();

    $actionPath = base_path('app/Domain/Design/Actions/SimpleAction.php');
    expect($actionPath)->toBeFile();

    $actionContents = file_get_contents($actionPath);
    expect($actionContents)
        ->toContain('namespace App\\Domain\\Design\\Actions;')
        ->toContain('class SimpleAction extends BaseAction')
        ->toContain("return 'simple';")
        ->toContain('public static function getDataType(): string')
        ->toContain('return Data::class;');
});

it('does not overwrite existing data dto', function () {
    $fs = new Filesystem();

    $dataDir = base_path('app/Domain/Design/Data');
    $fs->ensureDirectoryExists($dataDir);

    $existingDataPath = $dataDir . '/ExistingData.php';
    $existingContents = <<<'PHP'
<?php

namespace App\Domain\Design\Data;

use Spatie\LaravelData\Data;

class ExistingData extends Data
{
    public string $keep = 'me';
}
PHP;
    $fs->put($existingDataPath, $existingContents);

    // Run generator with existing DTO name
    artisan('make:action', [
        'name' => 'UseExistingDataAction',
        '--domain' => 'Design',
        '--data' => 'ExistingData',
    ])->assertSuccessful();

    // Assert action created
    $actionPath = base_path('app/Domain/Design/Actions/UseExistingDataAction.php');
    expect($actionPath)->toBeFile();

    // Existing DTO was not overwritten
    expect(file_get_contents($existingDataPath))->toBe($existingContents);
});
