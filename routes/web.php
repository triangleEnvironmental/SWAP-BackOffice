<?php

use App\Http\Controllers\Admin\AssignmentController;
use App\Http\Controllers\Admin\CitizenController;
use App\Http\Controllers\Admin\CkeditorController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\FaqCategoryController;
use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\Admin\MunicipalityController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\NotificationPresetController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReportTypeController;
use App\Http\Controllers\Admin\SectorController;
use App\Http\Controllers\Admin\ServiceProviderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VisibilityConfigController;
use App\Http\Controllers\GravatarController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Jetstream\Jetstream;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('gravatar', [GravatarController::class, 'generate']);

Route::get('about', [PageController::class, 'about'])->name('about.show');
Route::get('terms-of-service', [PageController::class, 'terms'])->name('terms.show');
Route::get('privacy-policy', [PageController::class, 'policy'])->name('policy.show');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::group(['prefix' => 'edit-page'], function () {
        Route::get('about', [PageController::class, 'editAbout'])->name('about.edit');
        Route::get('terms-of-service', [PageController::class, 'editTerms'])->name('terms.edit');
        Route::get('privacy-policy', [PageController::class, 'editPolicy'])->name('policy.edit');

        Route::put('{page_key}', [PageController::class, 'update'])->name('page.update');
    });

    Route::get('/', [DashboardController::class, 'dashboardPage'])
        ->name('dashboard');

    Route::post('/ckeditor/upload-adapter', [CkeditorController::class, 'upload_adapter'])
        ->name('upload-adapter');

    Route::group(['prefix' => 'faqs'], function () {
        Route::get('', [FaqController::class, 'listPage'])
            ->name('faq.list')
            ->can('view-faq');

        Route::post('', [FaqController::class, 'create'])
            ->name('faq.post')
            ->can('create-faq');

        Route::get('create', [FaqController::class, 'createPage'])
            ->name('faq.create')
            ->can('create-faq');

        Route::put('{id}', [FaqController::class, 'update'])
            ->name('faq.update')
            ->can('update-faq');

        Route::get('{id}/edit', [FaqController::class, 'editPage'])
            ->name('faq.edit')
            ->can('update-faq');

        Route::delete('{id}/delete', [FaqController::class, 'delete'])
            ->name('faq.delete')
            ->can('delete-faq');
    });

    Route::group(['prefix' => 'users'], function () {
        Route::get('', [UserController::class, 'listPage'])
            ->name('user.list')
            ->can('view-user');

        Route::get('create', [UserController::class, 'createPage'])
            ->name('user.create')
            ->can('create-user');

        Route::post('', [UserController::class, 'create'])
            ->name('user.post')
            ->can('create-user');

        Route::delete('{id}/photo', [UserController::class, 'deletePhoto'])
            ->name('user-photo.delete')
            ->can('update-user');

        Route::get('{id}/edit', [UserController::class, 'editPage'])
            ->name('user.edit')
            ->can('update-user');

        Route::post('{id}/enable', [UserController::class, 'enable'])
            ->name('user.enable')
            ->can('enable-user');

        Route::post('{id}/disable', [UserController::class, 'disable'])
            ->name('user.disable')
            ->can('enable-user');

        Route::put('{id}', [UserController::class, 'update'])
            ->name('user.update')
            ->can('update-user');
    });

    Route::group(['prefix' => 'citizens'], function () {
        Route::get('', [CitizenController::class, 'listPage'])
            ->name('citizen.list')
            ->can('view-citizen');
    });

    Route::group(['prefix' => 'service-providers'], function () {
        Route::get('', [ServiceProviderController::class, 'listPage'])
            ->name('service-provider.list')
            ->can('view-service-provider');

        Route::post('', [ServiceProviderController::class, 'create'])
            ->name('service-provider.post')
            ->can('create-service-provider');

        Route::get('create', [ServiceProviderController::class, 'createPage'])
            ->name('service-provider.create')
            ->can('create-service-provider');

        Route::group(['prefix' => '{id}'], function () {
            Route::put('', [ServiceProviderController::class, 'update'])
                ->name('service-provider.update')
                ->can('update-service-provider');

            Route::delete('logo', [ServiceProviderController::class, 'deleteLogo'])
                ->name('service-provider-logo.delete')
                ->can('update-service-provider');

            Route::get('edit', [ServiceProviderController::class, 'editPage'])
                ->name('service-provider.edit')
                ->can('update-service-provider');

            Route::get('edit-my', [ServiceProviderController::class, 'editMyServiceProviderPage'])
                ->name('my-service-provider.edit')
                ->can('view-own-service-provider');

            Route::delete('delete', [ServiceProviderController::class, 'delete'])
                ->name('service-provider.delete')
                ->can('delete-service-provider');

            Route::get('area', [ServiceProviderController::class, 'areaPage'])
                ->name('service-provider-area.list')
                ->can('update-service-provider-area');

            Route::post('area', [ServiceProviderController::class, 'createArea'])
                ->name('service-provider-area.create')
                ->can('update-service-provider-area');

            Route::put('area/{area_id}', [ServiceProviderController::class, 'updateArea'])
                ->name('service-provider-area.update')
                ->can('update-service-provider-area');

            Route::delete('area/{area_id}', [ServiceProviderController::class, 'deleteArea'])
                ->name('service-provider-area.delete')
                ->can('update-service-provider-area');
        });
    });

    Route::group(['prefix' => 'municipalities'], function () {
        Route::get('', [MunicipalityController::class, 'listPage'])
            ->name('municipality.list')
            ->can('view-municipality');

        Route::get('create', [MunicipalityController::class, 'createPage'])
            ->name('municipality.create')
            ->can('create-municipality');

        Route::post('', [MunicipalityController::class, 'create'])
            ->name('municipality.post')
            ->can('create-municipality');

        Route::group(['prefix' => '{id}'], function () {
            Route::put('', [MunicipalityController::class, 'update'])
                ->name('municipality.update')
                ->can('update-municipality');

            Route::delete('logo', [MunicipalityController::class, 'deleteLogo'])
                ->name('municipality-logo.delete')
                ->can('update-municipality');

            Route::get('edit', [MunicipalityController::class, 'editPage'])
                ->name('municipality.edit')
                ->can('update-municipality');

            Route::get('edit-my', [MunicipalityController::class, 'editMyMunicipalityPage'])
                ->name('my-municipality.edit')
                ->can('view-own-municipality');

            Route::delete('delete', [MunicipalityController::class, 'delete'])
                ->name('municipality.delete')
                ->can('delete-municipality');

            Route::get('area', [MunicipalityController::class, 'areaPage'])
                ->name('municipality-area.list')
                ->can('update-municipality-area');

            Route::post('area', [MunicipalityController::class, 'createArea'])
                ->name('municipality-area.create')
                ->can('update-municipality-area');

            Route::put('area/{area_id}', [MunicipalityController::class, 'updateArea'])
                ->name('municipality-area.update')
                ->can('update-municipality-area');

            Route::delete('area/{area_id}', [MunicipalityController::class, 'deleteArea'])
                ->name('municipality-area.delete')
                ->can('update-municipality-area');
        });
    });

    Route::group(['prefix' => 'sectors'], function () {
        Route::get('', [SectorController::class, 'listPage'])
            ->name('sector.list')
            ->can('view-sector');

        Route::post('', [SectorController::class, 'create'])
            ->name('sector.post')
            ->can('create-sector');

        Route::get('create', [SectorController::class, 'createPage'])
            ->name('sector.create')
            ->can('create-sector');

        Route::delete('{id}/icon', [SectorController::class, 'deleteIcon'])
            ->name('sector-icon.delete')
            ->can('update-sector');

        Route::get('{id}/edit', [SectorController::class, 'editPage'])
            ->name('sector.edit')
            ->can('update-sector');

        Route::delete('{id}/delete', [SectorController::class, 'delete'])
            ->name('sector.delete')
            ->can('delete-sector');

        Route::put('{id}', [SectorController::class, 'update'])
            ->name('sector.update')
            ->can('update-sector');
    });

    Route::group(['prefix' => 'report-types'], function () {
        Route::get('', [ReportTypeController::class, 'listPage'])
            ->name('report-type.list')
            ->can('view-report-type');

        Route::post('', [ReportTypeController::class, 'create'])
            ->name('report-type.post')
            ->can('create-report-type');

        Route::get('create', [ReportTypeController::class, 'createPage'])
            ->name('report-type.create')
            ->can('create-report-type');

        Route::get('{id}/edit', [ReportTypeController::class, 'editPage'])
            ->name('report-type.edit')
            ->can('update-report-type');

        Route::delete('{id}/delete', [ReportTypeController::class, 'delete'])
            ->name('report-type.delete')
            ->can('delete-report-type');

        Route::put('{id}', [ReportTypeController::class, 'update'])
            ->name('report-type.update')
            ->can('update-report-type');
    });

    Route::group(['prefix' => 'reports'], function () {
        Route::get('', [ReportController::class, 'listPage'])
            ->name('report.list')
            ->can('view-report');

        Route::get('map', [ReportController::class, 'mapPage'])
            ->name('report.map')
            ->can('view-report');

        Route::get('map-query', [ReportController::class, 'mapQuery'])
            ->name('report.map.query')
            ->can('view-report');

        Route::get('export-csv', [ReportController::class, 'exportCsv'])
            ->name('report.export.csv')
            ->can('export-report-csv');

        Route::get('{id}', [ReportController::class, 'detailPage'])
            ->name('report.show')
            ->can('view-report');

        Route::delete('{id}', [ReportController::class, 'delete'])
            ->name('report.delete')
            ->can('delete-report');
    });

    Route::group(['prefix' => 'faq-categories'], function () {
        Route::get('', [FaqCategoryController::class, 'listPage'])
            ->name('faq-category.list')
            ->can('view-faq-category');

        Route::post('', [FaqCategoryController::class, 'create'])
            ->name('faq-category.post')
            ->can('create-faq-category');

        Route::get('create', [FaqCategoryController::class, 'createPage'])
            ->name('faq-category.create')
            ->can('create-faq-category');

        Route::get('{id}/edit', [FaqCategoryController::class, 'editPage'])
            ->name('faq-category.edit')
            ->can('update-faq-category');

        Route::delete('{id}/delete', [FaqCategoryController::class, 'delete'])
            ->name('faq-category.delete')
            ->can('delete-faq-category');

        Route::put('{id}', [FaqCategoryController::class, 'update'])
            ->name('faq-category.update')
            ->can('update-faq-category');
    });

    Route::group(['prefix' => 'notifications'], function () {
        Route::get('', [NotificationController::class, 'listPage'])
            ->name('notification.list')
            ->can('view-notification');

        Route::get('form-options', [NotificationController::class, 'notification_form_options'])
            ->name('notification.form')
            ->can('create-notification');

        Route::post('send', [NotificationController::class, 'send_a_notification'])
            ->name('notification.create')
            ->can('create-notification');

        Route::post('area/{area_id}', [NotificationController::class, 'send_to_area'])
            ->name('notification-to-area.create')
            ->can('create-notification');
    });

    Route::group(['prefix' => 'notification-presets'], function () {
        Route::get('', [NotificationPresetController::class, 'listPage'])
            ->name('notification-preset.list')
            ->can('view-notification-preset');

        Route::post('', [NotificationPresetController::class, 'create'])
            ->name('notification-preset.post')
            ->can('create-notification-preset');

        Route::get('create', [NotificationPresetController::class, 'createPage'])
            ->name('notification-preset.create')
            ->can('create-notification-preset');

        Route::get('options', [NotificationPresetController::class, 'listOptions'])
            ->name('notification-preset.options');

        Route::get('{id}/edit', [NotificationPresetController::class, 'editPage'])
            ->name('notification-preset.edit')
            ->can('update-notification-preset');

        Route::delete('{id}/delete', [NotificationPresetController::class, 'delete'])
            ->name('notification-preset.delete')
            ->can('delete-notification-preset');

        Route::put('{id}', [NotificationPresetController::class, 'update'])
            ->name('notification-preset.update')
            ->can('update-notification-preset');
    });

    Route::group(['prefix' => 'visibility-configs'], function () {
        Route::get('', [VisibilityConfigController::class, 'index'])
            ->name('visibility-config.list')
            ->can('configure-report-visibility-duration');

        Route::put('', [VisibilityConfigController::class, 'update'])
            ->name('visibility-config.update')
            ->can('configure-report-visibility-duration');
    });

    Route::group(['prefix' => 'assignments'], function () {
        Route::get('{report_id}/users', [AssignmentController::class, 'assignable_users'])
            ->name('assignment-user.list')
            ->can('assign-report');

        Route::post('{report_id}', [AssignmentController::class, 'assign'])
            ->name('assign.post')
            ->can('assign-report');
    });

    Route::group(['prefix' => 'comments'], function () {
        Route::post('reports/{report_id}', [CommentController::class, 'create'])
            ->name('comment.post')
            ->can('moderate-report');

        Route::get('reports/{report_id}', [CommentController::class, 'list'])
            ->name('comment.list')
            ->can('view-report');

        Route::delete('{id}', [CommentController::class, 'delete'])
            ->name('comment.delete')
            ->can('delete-comment');
    });

    Route::group(['prefix' => 'moderation'], function () {
        Route::post('reports/{report_id}', [ModerationController::class, 'moderate'])
            ->name('moderation.post')
            ->can('moderate-report');
    });
});
