<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\DocumentReviewService;
use App\Services\DocumentWorkflowService;
use App\Services\DocumentDownloadService;
use App\Services\DocumentRequestService;
use App\Repositories\DocumentReviewRepository;
use App\ViewModels\DocumentReviewViewModel;

class DocumentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DocumentReviewRepository::class);
        $this->app->singleton(DocumentReviewViewModel::class);

        $this->app->singleton(DocumentReviewService::class, function ($app) {
            return new DocumentReviewService(
                $app->make(DocumentReviewRepository::class),
                $app->make(DocumentReviewViewModel::class)
            );
        });

        $this->app->singleton(DocumentWorkflowService::class);
        $this->app->singleton(DocumentDownloadService::class);

        $this->app->singleton(DocumentRequestService::class, function ($app) {
            return new DocumentRequestService(
                $app->make(DocumentReviewRepository::class)
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
