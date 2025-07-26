<?php

namespace Deployer;

require 'recipe/laravel.php';

// Configurations
set('repository', 'https://github.com/VincentNdegwa/CLISP.git');
set('keep_releases', 2);


// Shared files/dirs (these persist between deployments)
add('shared_files', ['.env']);
add('shared_dirs', ['storage']);
add('writable_dirs', ['storage', 'bootstrap/cache', 'public/build']);

host('clisp')
    ->set('hostname', '164.92.89.75')
    ->set('remote_user', 'root')
    ->set('deploy_path', '/var/www/clisp');

// Tasks

// Clear previous build
task('deploy:clear_old_build', function () {
    run('rm -rf {{release_path}}/public/build');
});

// Build frontend assets using pnpm
task('build:assets', function () {
    writeln('<info>Checking Node, PNPM versions...</info>');
    run('node -v');
    run('pnpm -v');

    writeln('<info>Installing dependencies with pnpm...</info>');
    run('cd {{release_path}} && pnpm install >> build.log 2>&1');

    writeln('<info>Building assets with pnpm...</info>');
    run('cd {{release_path}} && NODE_OPTIONS="--max-old-space-size=2048" pnpm build >> build.log 2>&1');
});

// Install Composer dependencies
task('deploy:composer', function () {
    run('cd {{release_path}} && composer install --no-dev --prefer-dist --optimize-autoloader');
});

// Run Laravel migrations
task('deploy:migrate', function () {
    run('cd {{release_path}} && php artisan migrate --force');
});

// Set permissions
task('deploy:permissions', function () {
    run('cd {{release_path}} && chown -R www-data:www-data storage bootstrap/cache');
});

// Fix Laravel config cache issue
after('artisan:config:cache', 'artisan:config:clear');

// Hooks (after deploy tasks)
after('deploy:failed', 'deploy:unlock');

// Hook build task before switching symlink
before('build:assets', 'deploy:clear_old_build');
before('deploy:symlink', 'build:assets');

// Post-deploy actions
after('deploy:symlink', 'deploy:permissions');
after('deploy:symlink', 'deploy:migrate');

// after('deploy:symlink', 'supervisor:restart');

// task('supervisor:restart', function () {
//     run('sudo systemctl start supervisor');
//     run('sudo supervisorctl reread');
//     run('sudo supervisorctl update');
//     run('sudo supervisorctl restart clisp-worker:*');
// });
