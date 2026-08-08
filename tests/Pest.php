<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', fn () => $this->toBe(1));

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function adminPanelUser(): User
{
    $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    $user = User::factory()->create(['must_change_password' => false]);
    $user->assignRole($role);

    return $user;
}

/*
|--------------------------------------------------------------------------
| pestphp/pest-plugin-laravel compatibility shim
|--------------------------------------------------------------------------
|
| pest-plugin-laravel v4 moved its test helpers (actingAs, get, getJson, ...)
| under the `Pest\Laravel` namespace instead of defining them globally. Test
| files in this project call them unqualified (no `use function` import),
| which only resolves against the global namespace — so every one of these
| calls fails with "Call to undefined function" unless proxied here. These
| thin wrappers restore the previous global-function behavior for every
| helper actually used across the suite.
|
*/

if (! function_exists('actingAs')) {
    function actingAs(...$args)
    {
        return Pest\Laravel\actingAs(...$args);
    }
}

if (! function_exists('artisan')) {
    function artisan(...$args)
    {
        return Pest\Laravel\artisan(...$args);
    }
}

if (! function_exists('assertAuthenticated')) {
    function assertAuthenticated(...$args)
    {
        return Pest\Laravel\assertAuthenticated(...$args);
    }
}

if (! function_exists('assertGuest')) {
    function assertGuest(...$args)
    {
        return Pest\Laravel\assertGuest(...$args);
    }
}

if (! function_exists('assertAuthenticatedAs')) {
    function assertAuthenticatedAs(...$args)
    {
        return Pest\Laravel\assertAuthenticatedAs(...$args);
    }
}

if (! function_exists('assertDatabaseHas')) {
    function assertDatabaseHas(...$args)
    {
        return Pest\Laravel\assertDatabaseHas(...$args);
    }
}

if (! function_exists('call')) {
    function call(...$args)
    {
        return Pest\Laravel\call(...$args);
    }
}

if (! function_exists('assertSame')) {
    function assertSame(...$args)
    {
        return Assert::assertSame(...$args);
    }
}

if (! function_exists('assertNull')) {
    function assertNull(...$args)
    {
        return Assert::assertNull(...$args);
    }
}

if (! function_exists('assertNotNull')) {
    function assertNotNull(...$args)
    {
        return Assert::assertNotNull(...$args);
    }
}

if (! function_exists('get')) {
    function get(...$args)
    {
        return Pest\Laravel\get(...$args);
    }
}

if (! function_exists('getJson')) {
    function getJson(...$args)
    {
        return Pest\Laravel\getJson(...$args);
    }
}

if (! function_exists('post')) {
    function post(...$args)
    {
        return Pest\Laravel\post(...$args);
    }
}

if (! function_exists('postJson')) {
    function postJson(...$args)
    {
        return Pest\Laravel\postJson(...$args);
    }
}

if (! function_exists('put')) {
    function put(...$args)
    {
        return Pest\Laravel\put(...$args);
    }
}

if (! function_exists('putJson')) {
    function putJson(...$args)
    {
        return Pest\Laravel\putJson(...$args);
    }
}

if (! function_exists('patch')) {
    function patch(...$args)
    {
        return Pest\Laravel\patch(...$args);
    }
}

if (! function_exists('patchJson')) {
    function patchJson(...$args)
    {
        return Pest\Laravel\patchJson(...$args);
    }
}

if (! function_exists('delete')) {
    function delete(...$args)
    {
        return Pest\Laravel\delete(...$args);
    }
}

if (! function_exists('deleteJson')) {
    function deleteJson(...$args)
    {
        return Pest\Laravel\deleteJson(...$args);
    }
}

if (! function_exists('from')) {
    function from(...$args)
    {
        return Pest\Laravel\from(...$args);
    }
}

if (! function_exists('withSession')) {
    function withSession(...$args)
    {
        return Pest\Laravel\withSession(...$args);
    }
}
