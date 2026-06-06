<?php

namespace App\Console\Commands;

use App\Enums\Status;
use App\Models\User;
use Database\Seeders\EmployeeRoleSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class InstallApplication extends Command
{
    protected $signature = 'app:install
                            {--email=admin@julius.test : Admin email address}
                            {--password= : Admin password (generated randomly if omitted)}
                            {--name=Administrator : Admin display name}
                            {--url=http://julius-fitness-gym.test : Application URL}
                            {--force : Reset admin password when the user already exists}';

    protected $description = 'Finalize installation: environment, admin user, and credentials file';

    public function handle(): int
    {
        $this->ensureStoragePaths();
        $this->configureEnvironment(
            url: (string) $this->option('url'),
            name: (string) $this->option('name'),
        );

        $email = strtolower((string) $this->option('email'));
        $explicitPassword = (string) $this->option('password');
        $passwordGenerated = $explicitPassword === '';
        $password = $passwordGenerated ? Str::password(16) : $explicitPassword;

        $role = Role::query()->firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            $user = User::query()->create([
                'name' => (string) $this->option('name'),
                'email' => $email,
                'password' => $password,
                'status' => Status::Active,
            ]);
            $user->must_change_password = $passwordGenerated;
            $user->save();
            $this->info("Admin user created: {$email}");
        } else {
            if ((bool) $this->option('force')) {
                $user->name = (string) $this->option('name');
                $user->password = $password;
                $user->must_change_password = $passwordGenerated;
                $user->save();
                $this->warn("Admin user updated: {$email}");
            } else {
                $this->warn("Admin user already exists: {$email}");
            }
        }

        if (! $user->hasRole($role->name)) {
            $user->assignRole($role);
        }

        $this->ensureShieldPermissions();
        (new EmployeeRoleSeeder)->run();
        Artisan::call('permission:cache-reset');

        $this->writeCredentialsFile($email, $passwordGenerated);

        File::put(storage_path('app/.install-complete'), now()->toIso8601String());

        $this->newLine();
        $this->line(' Application URL: '.(string) $this->option('url'));
        $this->line(' Admin panel: '.rtrim((string) $this->option('url'), '/').'/admin');
        $this->line(" Email: {$email}");
        $this->line(" Password: {$password}");

        if ($passwordGenerated) {
            $this->warn(' ⚠ Password was generated randomly. Save it now — it will NOT be stored.');
            $this->warn(' You will be prompted to change it on first login.');
        }

        $this->newLine();

        return self::SUCCESS;
    }

    private function ensureStoragePaths(): void
    {
        foreach ([
            storage_path('app'),
            storage_path('app/private'),
            storage_path('app/private/livewire-tmp'),
            storage_path('framework/views'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('logs'),
        ] as $directory) {
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
        }
    }

    private function configureEnvironment(string $url, string $name): void
    {
        $envPath = base_path('.env');
        $freshInstall = ! is_file($envPath);

        if ($freshInstall) {
            File::copy(base_path('.env.example'), $envPath);
        }

        $this->setEnvValue('APP_NAME', $name);
        $this->setEnvValue('APP_URL', $url);

        // Only seed install defaults on a brand-new .env — never overwrite an
        // already-configured environment (e.g. a MySQL dev setup). This keeps
        // standalone installs on SQLite while protecting existing setups and
        // test runs from having their database connection clobbered.
        if ($freshInstall) {
            $this->setEnvValue('APP_ENV', 'local');

            $envContent = File::get($envPath);
            if (! preg_match('/^DB_CONNECTION=mysql/m', $envContent)) {
                $this->setEnvValue('DB_CONNECTION', 'sqlite');
            }
        }
    }

    private function setEnvValue(string $key, string $value): void
    {
        $envPath = base_path('.env');
        $escaped = str_contains($value, ' ') ? '"'.$value.'"' : $value;
        $line = "{$key}={$escaped}";

        $content = File::get($envPath);

        if (preg_match("/^{$key}=/m", $content)) {
            $content = (string) preg_replace("/^{$key}=.*$/m", $line, $content);
        } else {
            $content = rtrim($content).PHP_EOL.$line.PHP_EOL;
        }

        File::put($envPath, $content);
    }

    private function ensureShieldPermissions(): void
    {
        if (Permission::query()->exists()) {
            return;
        }

        $this->info('Generating Filament Shield permissions...');
        $this->call('shield:generate', [
            '--all' => true,
            '--panel' => 'admin',
            '--option' => 'permissions',
            '--no-interaction' => true,
        ]);
    }

    private function writeCredentialsFile(string $email, bool $passwordGenerated): void
    {
        $passwordNote = $passwordGenerated
            ? 'Parola a fost generată aleator și afișată o singură dată în terminal.'
            : 'Parola a fost setată manual la instalare.';

        $body = implode(PHP_EOL, [
            'Julius Fitness Gym — Administrator',
            '====================================',
            'URL: '.(string) $this->option('url').'/admin',
            'Email: '.$email,
            '',
            $passwordNote,
            'Dacă ai uitat parola, folosește funcția "Forgot Password" din pagina de login.',
            '',
            'Generat: '.now()->toDateTimeString(),
            '',
        ]);

        File::put(storage_path('app/install-credentials.txt'), $body);
    }
}
