<?php

namespace App\Console\Commands;

use App\Enums\Status;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;

class InstallApplication extends Command
{
    protected $signature = 'app:install
                            {--email=admin@julius.test : Admin email address}
                            {--password=julius2024 : Admin password}
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
        $password = (string) $this->option('password');

        if ($password === '') {
            $this->error('Admin password cannot be empty.');

            return self::FAILURE;
        }

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
            $this->info("Admin user created: {$email}");
        } else {
            if ((bool) $this->option('force')) {
                $user->update([
                    'name' => (string) $this->option('name'),
                    'password' => $password,
                ]);
                $this->warn("Admin user updated: {$email}");
            } else {
                $this->warn("Admin user already exists: {$email}");
            }
        }

        if (! $user->hasRole($role->name)) {
            $user->assignRole($role);
        }

        $this->writeCredentialsFile($email, $password);
        File::put(storage_path('app/.install-complete'), now()->toIso8601String());

        $this->newLine();
        $this->line('  Application URL: '.(string) $this->option('url'));
        $this->line('  Admin panel:     '.rtrim((string) $this->option('url'), '/').'/admin');
        $this->line("  Email:           {$email}");
        $this->line("  Password:        {$password}");
        $this->newLine();
        $this->line('  Credentials saved to: storage/app/install-credentials.txt');

        return self::SUCCESS;
    }

    private function ensureStoragePaths(): void
    {
        foreach ([
            storage_path('app'),
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

        if (! is_file($envPath)) {
            File::copy(base_path('.env.example'), $envPath);
        }

        $this->setEnvValue('APP_NAME', $name);
        $this->setEnvValue('APP_URL', $url);
        $this->setEnvValue('APP_ENV', 'local');
        $this->setEnvValue('DB_CONNECTION', 'sqlite');
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

    private function writeCredentialsFile(string $email, string $password): void
    {
        $body = implode(PHP_EOL, [
            'Julius Fitness Gym — Administrator',
            '====================================',
            'URL:      '.(string) $this->option('url').'/admin',
            'Email:    '.$email,
            'Password: '.$password,
            '',
            'Schimbă parola după prima autentificare.',
            'Generat:  '.now()->toDateTimeString(),
            '',
        ]);

        File::put(storage_path('app/install-credentials.txt'), $body);
    }
}
