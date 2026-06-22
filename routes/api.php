<?php

/** API v1 — Julius Fitness Gym · @studio Daniel-OD */

use App\Http\Controllers\Api\V1\AnalyticsController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\EnquiriesController;
use App\Http\Controllers\Api\V1\EnquiryFollowUpsController;
use App\Http\Controllers\Api\V1\ExpensesController;
use App\Http\Controllers\Api\V1\FitnessController;
use App\Http\Controllers\Api\V1\FollowUpsController;
use App\Http\Controllers\Api\V1\GymClassesController;
use App\Http\Controllers\Api\V1\InvoicesController;
use App\Http\Controllers\Api\V1\InvoiceTransactionsController;
use App\Http\Controllers\Api\V1\MembersController;
use App\Http\Controllers\Api\V1\PermissionsController;
use App\Http\Controllers\Api\V1\PlansController;
use App\Http\Controllers\Api\V1\ProductsController;
use App\Http\Controllers\Api\V1\RolesController;
use App\Http\Controllers\Api\V1\SalesController;
use App\Http\Controllers\Api\V1\ServicesController;
use App\Http\Controllers\Api\V1\SettingsController;
use App\Http\Controllers\Api\V1\SubscriptionsController;
use App\Http\Controllers\Api\V1\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum')->name('api.user');

Route::prefix('v1')
    ->group(function (): void {
        Route::post('/auth/login', [AuthController::class, 'login'])
            ->middleware('throttle:api-login');

        Route::middleware(['auth:sanctum', 'throttle:api'])
            ->group(function (): void {
                Route::get('/me', [AuthController::class, 'me']);
                Route::post('/auth/logout', [AuthController::class, 'logout']);

                Route::get('/settings', [SettingsController::class, 'show']);
                Route::put('/settings', [SettingsController::class, 'update']);

                Route::prefix('analytics')->group(function (): void {
                    Route::get('/financial', [AnalyticsController::class, 'financial']);
                    Route::get('/membership', [AnalyticsController::class, 'membership']);
                    Route::get('/cashflow-trend', [AnalyticsController::class, 'cashflowTrend']);
                    Route::get('/expense-categories', [AnalyticsController::class, 'expenseCategories']);
                    Route::get('/top-plans', [AnalyticsController::class, 'topPlans']);
                    Route::get('/recent-transactions', [AnalyticsController::class, 'recentTransactions']);
                });

                Route::get('/roles', [RolesController::class, 'index']);
                Route::get('/permissions', [PermissionsController::class, 'index']);

                Route::apiResource('users', UsersController::class);
                Route::post('/users/{user}/restore', [UsersController::class, 'restore']);
                Route::delete('/users/{user}/force', [UsersController::class, 'forceDelete']);

                Route::apiResource('members', MembersController::class);
                Route::post('/members/{member}/restore', [MembersController::class, 'restore']);
                Route::delete('/members/{member}/force', [MembersController::class, 'forceDelete']);

                Route::apiResource('services', ServicesController::class);
                Route::post('/services/{service}/restore', [ServicesController::class, 'restore']);
                Route::delete('/services/{service}/force', [ServicesController::class, 'forceDelete']);

                Route::apiResource('plans', PlansController::class);
                Route::post('/plans/{plan}/restore', [PlansController::class, 'restore']);
                Route::delete('/plans/{plan}/force', [PlansController::class, 'forceDelete']);

                Route::apiResource('subscriptions', SubscriptionsController::class);
                Route::post('/subscriptions/{subscription}/restore', [SubscriptionsController::class, 'restore']);
                Route::delete('/subscriptions/{subscription}/force', [SubscriptionsController::class, 'forceDelete']);
                Route::post('/subscriptions/{subscription}/renew', [SubscriptionsController::class, 'renew']);

                Route::apiResource('invoices', InvoicesController::class);
                Route::post('/invoices/{invoice}/restore', [InvoicesController::class, 'restore']);
                Route::delete('/invoices/{invoice}/force', [InvoicesController::class, 'forceDelete']);
                Route::get('/invoices/{invoice}/pdf', [InvoicesController::class, 'pdf']);
                Route::get('/invoices/{invoice}/pdf/download', [InvoicesController::class, 'downloadPdf']);

                Route::get('/invoices/{invoice}/transactions', [InvoiceTransactionsController::class, 'index']);
                Route::post('/invoices/{invoice}/transactions', [InvoiceTransactionsController::class, 'store']);
                Route::delete('/invoices/{invoice}/transactions/{transaction}', [InvoiceTransactionsController::class, 'destroy']);

                Route::apiResource('expenses', ExpensesController::class);

                Route::get('/exercises', [FitnessController::class, 'exercises']);
                Route::get('/members/{member}/workout-plan', [FitnessController::class, 'memberWorkoutPlan']);
                Route::get('/members/{member}/nutrition-plan', [FitnessController::class, 'memberNutritionPlan']);
                Route::post('/workout-logs', [FitnessController::class, 'storeWorkoutLog']);
                Route::post('/food-logs', [FitnessController::class, 'storeFoodLog']);

                Route::get('/products', [ProductsController::class, 'index']);
                Route::get('/products/{product}', [ProductsController::class, 'show']);
                Route::get('/sales', [SalesController::class, 'index']);
                Route::post('/sales', [SalesController::class, 'store']);
                Route::get('/sales/{sale}', [SalesController::class, 'show']);

                Route::get('/classes', [GymClassesController::class, 'index']);
                Route::get('/classes/schedule', [GymClassesController::class, 'schedule']);
                Route::post('/bookings', [GymClassesController::class, 'book']);
                Route::get('/bookings', [GymClassesController::class, 'myBookings']);
                Route::delete('/bookings/{booking}', [GymClassesController::class, 'cancelBooking']);

                Route::apiResource('enquiries', EnquiriesController::class);
                Route::post('/enquiries/{enquiry}/restore', [EnquiriesController::class, 'restore']);
                Route::delete('/enquiries/{enquiry}/force', [EnquiriesController::class, 'forceDelete']);

                Route::get('/enquiries/{enquiry}/follow-ups', [EnquiryFollowUpsController::class, 'index']);
                Route::post('/enquiries/{enquiry}/follow-ups', [EnquiryFollowUpsController::class, 'store']);

                Route::apiResource('follow-ups', FollowUpsController::class)
                    ->parameters(['follow-ups' => 'followUp']);
                Route::post('/follow-ups/{followUp}/restore', [FollowUpsController::class, 'restore']);
                Route::delete('/follow-ups/{followUp}/force', [FollowUpsController::class, 'forceDelete']);
            });
    });
