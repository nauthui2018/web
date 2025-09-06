<?php

namespace App\Providers;

use App\Contracts\CertificateGeneratorInterface;
use App\Contracts\CertificateTemplateInterface;
use App\Http\Middleware\JWTAuthenticate;
use App\Services\Certificate\Generators\PdfCertificateGenerator;
use App\Services\Certificate\Templates\DefaultCertificateTemplate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->bindInterfaceImplementation();
        $this->bindServiceInterfaces();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Override JWT Auth package's middleware aliases with our custom ones
        $router = $this->app['router'];
        $router->aliasMiddleware('jwt.auth', JWTAuthenticate::class);
    }

    private function bindInterfaceImplementation(): void
    {
        $interfaceImplementations = config('interface-implementations', []);

        foreach ($interfaceImplementations as $interface => $service) {
            $interfaceClass = "App\Repositories\\$interface";
            $serviceClass = "App\Repositories\\$service";
            $this->app->bind($interfaceClass, $serviceClass);
        }
    }

    private function bindServiceInterfaces(): void
    {
        // Bind certificate generation interfaces (strategy pattern)
        $this->app->bind(
            CertificateGeneratorInterface::class,
            PdfCertificateGenerator::class
        );

        // Bind template interface - default template
        $this->app->bind(
            CertificateTemplateInterface::class,
            DefaultCertificateTemplate::class
        );
    }
}
