<?php
namespace Deployer;

/*Modif 1*/
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Dotenv\Dotenv;


require 'recipe/symfony4.php';

/*Modif 3*/

set('symfony_env', 'prod');

// Project name
set('application', 'pear'); /*Modif 4*/

// Project repository
set('repository', 'https://github.com/raudut/Pear');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true); 

// Hosts

host('pear.min.epf.fr') /*Modif 5*/
    ->user('min')
    //->password('min.epf.fr2020')
    ->port(2247)
    ->set('deploy_path', '/data/www/{{application}}');
    
// Modif 7: Nombre de déploiements à conserver avant de les supprimer.
set('keep_releases', 4);


set('composer_options', '{{composer_action}} --verbose --prefer-dist --no-progress --no-interaction --optimize-autoloader --no-suggest');



task('deploy:assets:install', function () {
    run('{{bin/php}} {{bin/console}} assets:install {{console_options}} --symlink');
})->desc('Install bundle assets');

task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:clear_paths',
    'deploy:shared',
    'deploy:vendors',
    'deploy:cache:clear',
    'deploy:cache:warmup',
    'deploy:writable',
    'deploy:assets:install',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
])->desc('Deploy your project');


// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

before('deploy:symlink', 'database:migrate');

