<?php
namespace Grav\Plugin;

use Clockwork\Request\Timeline\Event;
use Grav\Common\Plugin;

class BlackholePlugin extends Plugin {
  public $content;
  public static function getSubscribedEvents() {
    return [
      'onPageInitialized' => ['onPageInitialized', 0],
      'onOutputRendered' => ['onOutputRendered', 0],
      'PluginsLoadedEvent'
    ];
  }

  public function onPageInitialized() {
    if (!defined('ROOT_URL')) define('ROOT_URL', $this->grav['uri']->rootUrl(true));

    if (!empty($_GET['blackhole']) && $_GET['blackhole'] === 'generate') {
      $input_url   = $this->config->get('plugins.blackhole.generate.input_url');
      $output_url   = $this->config->get('plugins.blackhole.generate.output_url');
      $output_path  = $this->config->get('plugins.blackhole.generate.output_path');
      $routes       = $this->config->get('plugins.blackhole.generate.routes');
      $simultaneous = $this->config->get('plugins.blackhole.generate.simultaneous');
      $assets       = $this->config->get('plugins.blackhole.generate.assets');
      $force        = $this->config->get('plugins.blackhole.generate.force');

      //dd($this->grav['language']);

//       $output_abs_path = $this->getOutputAbsPath($output_path);
// //      dd($output_abs_path);
//         // is directory output_path exist ?
//       if(!is_dir($output_abs_path)) {
// //        try {
//           if(!mkdir($output_abs_path, 0755, true)) { 
//             throw new \Exception('Échec lors de la création du dossier output_path : ' . $output_abs_path);
//           }
//         // } catch(\Exception $e) {
//         //   $this->grav['admin']->json_response = ['status' => 'error', 'message' => $e];
//         //   dd($this->grav['admin']);
//         // }
//       }

      $this->content =
        'bin/plugin blackhole generate ' . (isset($input_url) && trim($input_url) !== '' ? $input_url : ROOT_URL) .
        ($output_url   ? ' --output-url '   . $output_url   : '') .
        ($output_path  ? ' --output-path '  . $output_path  : '') .
        ($routes       ? ' --routes '       . $routes       : '') .
        ($simultaneous ? ' --simultaneous ' . $simultaneous : '') .
        ($assets       ? ' --assets'                        : '') .
        ($force        ? ' --force'                         : '')
      ;
    }
  }

  public function onOutputRendered() {
    // action for generate button
    if (!empty($_GET['blackhole']) && $_GET['blackhole'] === 'generate') {
      shell_exec($this->content);
    }
  }

  public function getOutputAbsPath(string $output_path): string
  {
    // $output_path fait référence pa rapport au dossier root du projet grav
    $root_project_abs_path = dirname(dirname(dirname(dirname(__FILE__))));
    $outputAbsPath = '';
    // si output_path commence par un / c'est un absolute dir et on ne retraite pas la chaine de caractère
    if(str_starts_with($output_path, '..')) {
      $output_path = explode('/', $output_path);
      foreach($output_path as $key => $dir_part) {
        if(trim($dir_part) !== '' && $dir_part === '..') {
          // on remonte d'un répertoire
          $root_project_abs_path =  dirname($root_project_abs_path);
          unset($output_path[$key]);
        }
      }
      $outputAbsPath = $root_project_abs_path . '/' . implode('/', $output_path);
    } elseif(!str_starts_with($output_path, '/')) {
      $outputAbsPath = $root_project_abs_path . '/' . $output_path;
    } else {
      $outputAbsPath = $output_path;
    }
    return $outputAbsPath;
  }
}
