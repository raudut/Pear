<?php
namespace Deployer;

require 'recipe/symfony.php';

// Project name
set('application', 'pear');
set('composer_options', 'install --verbose --prefer-dist --no-progress --no-interaction --optimize-autoloader');


// Project repository
set('repository', 'https://github.com/raudut/pear_project.git');
set('shared_files', []);
// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', false); 

// Shared files/dirs between deploys 
add('shared_files', []);
add('shared_dirs', []);

// Writable dirs by web server 
add('writable_dirs', []);
set('allow_anonymous_stats', false);

// Hosts

host('pear.min.epf.fr')
    ->user('min')
    ->port(2247)
    ->set('bin/php', 'php')
->set('bin/composer', 'composer')
    ->set('deploy_path', '/var/www/html/PearProject');
   
// Tasks

task('build', function () {
    run('cd {{release_path}} && build');
});

task('test', function() {
    writeln('Hello world');
});

task('pwd', function() {
    $result = run('pwd');
    writeln("Current dir: $result");
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

before('deploy:symlink', 'database:migrate');

