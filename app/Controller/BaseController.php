<?php

namespace Apteasy\Controller;

use Apteasy\Library\Breadcrumbs;
use Buki\Router\Http\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class BaseController extends Controller
{
    protected Environment $view;
    protected Breadcrumbs $breadcrumbs;

    public function __construct()
    {
        $loader = new FilesystemLoader(APP_PATH .'/views/');
        $this->view = new Environment($loader, [
            'cache' => APP_PATH . '/storage/cache/views',
            'auto_reload' => true,
            'debug' => true
        ]);
        $this->view->addFunction(new TwigFunction('formatMoney', 'format_money'));

        $this->breadcrumbs = new Breadcrumbs();
    }

    public function view($path, $args=[]): string
    {
        $args['user'] = session('user');
        $args['flash'] = (new Session())->getFlashBag()->all();
        $args['siteName'] = config('SITE_NAME');
        $args['breadcrumbs'] = $this->breadcrumbs;

        $fullPath = $this->className() . DIRECTORY_SEPARATOR . $path . '.twig';
        return $this->view->render($fullPath, $args);
    }

    public function className(): string
    {
        $className = (new \ReflectionClass(get_called_class()))->getName();
        $className = str_replace(['Apteasy\\Controller\\', 'Controller'], '', $className);

        return strtolower(str_replace('\\', DIRECTORY_SEPARATOR, $className));
    }
}