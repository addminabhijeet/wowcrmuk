<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\LoginsController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\GoogleSheetController;
use App\Http\Controllers\CallReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TimerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SmtpSettingController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\TimerApiController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\CandidateDetailsController;




Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard/admin/index', [UserController::class, 'index'])->name('users.admin');
    Route::get('/dashboard/admin/create', [UserController::class, 'admincreate'])->name('users.admin.create');
    Route::post('/dashboard/admin/store', [UserController::class, 'adminstore'])->name('users.admin.store');
    Route::get('/dashboard/admin/{id}/edit', [UserController::class, 'adminedit'])->name('users.admin.edit');
    Route::put('/dashboard/admin/update/{id}', [UserController::class, 'adminupdate'])->name('users.admin.update');
    Route::get('/dashboard/admin/destroy/{id}', [UserController::class, 'admindestroy'])->name('users.admin.destroy');

    Route::get('/dashboard/admin/junior', [UserController::class, 'junior'])->name('users.junior');
    Route::get('/dashboard/admin/junior/create', [UserController::class, 'juniorcreate'])->name('users.junior.create');
    Route::post('/dashboard/admin/junior/store', [UserController::class, 'juniorstore'])->name('users.junior.store');
    Route::get('/dashboard/admin/junior/{id}/edit', [UserController::class, 'junioredit'])->name('users.junior.edit');
    Route::put('/dashboard/admin/junior/update/{id}', [UserController::class, 'juniorupdate'])->name('users.junior.update');
    Route::get('/dashboard/admin/junior/destroy/{id}', [UserController::class, 'juniordestroy'])->name('users.junior.destroy');

    Route::get('/dashboard/admin/senior', [UserController::class, 'senior'])->name('users.senior');
    Route::get('/dashboard/admin/senior/create', [UserController::class, 'seniorcreate'])->name('users.senior.create');
    Route::post('/dashboard/admin/senior/store', [UserController::class, 'seniorstore'])->name('users.senior.store');
    Route::get('/dashboard/admin/senior/{id}/edit', [UserController::class, 'senioredit'])->name('users.senior.edit');
    Route::put('/dashboard/admin/senior/update/{id}', [UserController::class, 'seniorupdate'])->name('users.senior.update');
    Route::get('/dashboard/admin/senior/destroy/{id}', [UserController::class, 'seniordestroy'])->name('users.senior.destroy');

    Route::get('/dashboard/admin/trainer', [UserController::class, 'trainer'])->name('users.trainer');
    Route::get('/dashboard/admin/trainer/create', [UserController::class, 'trainercreate'])->name('users.trainer.create');
    Route::post('/dashboard/admin/trainer/store', [UserController::class, 'trainerstore'])->name('users.trainer.store');
    Route::get('/dashboard/admin/trainer/{id}/edit', [UserController::class, 'traineredit'])->name('users.trainer.edit');
    Route::put('/dashboard/admin/trainer/update/{id}', [UserController::class, 'trainerupdate'])->name('users.trainer.update');
    Route::get('/dashboard/admin/trainer/update/{id}', [UserController::class, 'trainerdestroy'])->name('users.trainer.destroy');

    Route::get('/dashboard/admin/accountant', [UserController::class, 'accountant'])->name('users.accountant');
    Route::get('/dashboard/admin/accountant/create', [UserController::class, 'accountantcreate'])->name('users.accountant.create');
    Route::post('/dashboard/admin/accountant/store', [UserController::class, 'accountantstore'])->name('users.accountant.store');
    Route::get('/dashboard/admin/accountant/{id}/edit', [UserController::class, 'accountantedit'])->name('users.accountant.edit');
    Route::put('/dashboard/admin/accountant/update/{id}', [UserController::class, 'accountantupdate'])->name('users.accountant.update');
    Route::get('/dashboard/admin/accountant/update/{id}', [UserController::class, 'accountantdestroy'])->name('users.accountant.destroy');

    Route::get('/dashboard/admin/associate', [UserController::class, 'associate'])->name('users.associate');
    Route::get('/dashboard/admin/associate/create', [UserController::class, 'associatecreate'])->name('users.associate.create');
    Route::post('/dashboard/admin/associate/store', [UserController::class, 'associatestore'])->name('users.associate.store');
    Route::get('/dashboard/admin/associate/{id}/edit', [UserController::class, 'associateedit'])->name('users.associate.edit');
    Route::put('/dashboard/admin/associate/update/{id}', [UserController::class, 'associateupdate'])->name('users.associate.update');
    Route::get('/dashboard/admin/associate/update/{id}', [UserController::class, 'associatedestroy'])->name('users.associate.destroy');

    Route::get('/dashboard/admin/seniorassociate', [UserController::class, 'seniorassociate'])->name('users.seniorassociate');
    Route::get('/dashboard/admin/seniorassociate/create', [UserController::class, 'seniorassociatecreate'])->name('users.seniorassociate.create');
    Route::post('/dashboard/admin/seniorassociate/store', [UserController::class, 'seniorassociatestore'])->name('users.seniorassociate.store');
    Route::get('/dashboard/admin/seniorassociate/{id}/edit', [UserController::class, 'seniorassociateedit'])->name('users.seniorassociate.edit');
    Route::put('/dashboard/admin/seniorassociate/update/{id}', [UserController::class, 'seniorassociateupdate'])->name('users.seniorassociate.update');
    Route::get('/dashboard/admin/seniorassociate/update/{id}', [UserController::class, 'seniorassociatedestroy'])->name('users.seniorassociate.destroy');

    Route::get('/dashboard/admin/operation', [UserController::class, 'operation'])->name('users.operation');
    Route::get('/dashboard/admin/operation/create', [UserController::class, 'operationcreate'])->name('users.operation.create');
    Route::post('/dashboard/admin/operation/store', [UserController::class, 'operationstore'])->name('users.operation.store');
    Route::get('/dashboard/admin/operation/{id}/edit', [UserController::class, 'operationedit'])->name('users.operation.edit');
    Route::put('/dashboard/admin/operation/update/{id}', [UserController::class, 'operationupdate'])->name('users.operation.update');
    Route::get('/dashboard/admin/operation/update/{id}', [UserController::class, 'operationdestroy'])->name('users.operation.destroy');

    Route::get('/dashboard/admin/resource', [UserController::class, 'resource'])->name('users.resource');
    Route::get('/dashboard/admin/resource/create', [UserController::class, 'resourcecreate'])->name('users.resource.create');
    Route::post('/dashboard/admin/resource/store', [UserController::class, 'resourcestore'])->name('users.resource.store');
    Route::get('/dashboard/admin/resource/{id}/edit', [UserController::class, 'resourceedit'])->name('users.resource.edit');
    Route::put('/dashboard/admin/resource/update/{id}', [UserController::class, 'resourceupdate'])->name('users.resource.update');
    Route::get('/dashboard/admin/resource/update/{id}', [UserController::class, 'resourcedestroy'])->name('users.resource.destroy');

    Route::get('/dashboard/admin/support', [UserController::class, 'support'])->name('users.support');
    Route::get('/dashboard/admin/support/create', [UserController::class, 'supportcreate'])->name('users.support.create');
    Route::post('/dashboard/admin/support/store', [UserController::class, 'supportstore'])->name('users.support.store');
    Route::get('/dashboard/admin/support/{id}/edit', [UserController::class, 'supportedit'])->name('users.support.edit');
    Route::put('/dashboard/admin/support/update/{id}', [UserController::class, 'supportupdate'])->name('users.support.update');
    Route::get('/dashboard/admin/support/update/{id}', [UserController::class, 'supportdestroy'])->name('users.support.destroy');

    Route::get('/dashboard/admin/writer', [UserController::class, 'writer'])->name('users.writer');
    Route::get('/dashboard/admin/writer/create', [UserController::class, 'writtercreate'])->name('users.writer.create');
    Route::post('/dashboard/admin/writer/store', [UserController::class, 'writterstore'])->name('users.writer.store');
    Route::get('/dashboard/admin/writer/{id}/edit', [UserController::class, 'writteredit'])->name('users.writer.edit');
    Route::put('/dashboard/admin/writer/update/{id}', [UserController::class, 'writterupdate'])->name('users.writer.update');
    Route::get('/dashboard/admin/writer/update/{id}', [UserController::class, 'writterdestroy'])->name('users.writer.destroy');

    Route::get('/dashboard/admin/customer', [UserController::class, 'customer'])->name('users.customer');
    Route::get('/dashboard/admin/customer/create', [UserController::class, 'customercreate'])->name('users.customer.create');
    Route::post('/dashboard/admin/customer/store', [UserController::class, 'customerstore'])->name('users.customer.store');
    Route::get('/dashboard/admin/customer/{id}/edit', [UserController::class, 'customeredit'])->name('users.customer.edit');
    Route::put('/dashboard/admin/customer/update/{id}', [UserController::class, 'customerupdate'])->name('users.customer.update');
    Route::get('/dashboard/admin/customer/destroy/{id}', [UserController::class, 'customerdestroy'])->name('users.customer.destroy');
    Route::get('/dashboard/admin/latest-notification', [DashboardController::class, 'latestNotification'])->name('admin.latest.notification');
    Route::put('/dashboard/admin/latest-markallread', [DashboardController::class, 'markAllRead'])->name('admin.notifications.markallread');
    Route::get('/dashboard/notification', [DashboardController::class, 'adminnotification'])->name('admin.notifications');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.admin');
    Route::get('/dashboard/junior', [DashboardController::class, 'junior'])->name('dashboard.junior');
    Route::get('/dashboard/senior', [DashboardController::class, 'senior'])->name('dashboard.senior');
    Route::get('/dashboard/customer', [DashboardController::class, 'customer'])->name('dashboard.customer');
    Route::get('/dashboard/career', [DashboardController::class, 'career'])->name('dashboard.career');
    Route::get('/dashboard/accountant', [DashboardController::class, 'accountant'])->name('dashboard.accountant');
    Route::get('/dashboard/trainer', [DashboardController::class, 'trainer'])->name('dashboard.trainer');
    Route::get('/dashboard/support', [DashboardController::class, 'support'])->name('dashboard.support');
    Route::get('/dashboard/writer', [DashboardController::class, 'writer'])->name('dashboard.writer');
    Route::get('/dashboard/resource', [DashboardController::class, 'resource'])->name('dashboard.resource');
    Route::get('/dashboard/operation', [DashboardController::class, 'operation'])->name('dashboard.operation');
    Route::get('/button/status', [DashboardController::class, 'getButtonStatus'])->name('button.status');
    Route::post('/dashboard/start-timer', [DashboardController::class, 'startTimer'])->name('timer.start');
    Route::post('/dashboard/start-timer-hide', [DashboardController::class, 'startTimerHide'])->name('timer.starthide');
    Route::post('/dashboard/check-pause-buttons', [DashboardController::class, 'checkPauseButtons'])->name('timer.checkPauseButtons');
    Route::post('/dashboard/check-pause-buttons-senior', [DashboardController::class, 'checkPauseButtonsSenior'])->name('timer.checkPauseButtonsSenior');

    Route::get('/dashboard/admin/calendar/{month?}/{year?}', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/dashboard/accountant/calendar/{month?}/{year?}', [CalendarController::class, 'accountantUser'])->name('calendar.accountantUser');
    Route::get('/dashboard/trainer/calendar/{month?}/{year?}', [CalendarController::class, 'trainerUser'])->name('calendar.trainerUser');
    Route::get('/dashboard/junior/calendar/', [CalendarController::class, 'juniorUser'])->name('calendar.junior');
    Route::get('/dashboard/junior/calendar/events', [CalendarController::class, 'juniorEvents'])->name('calendar.juniorEvents');
    Route::get('/dashboard/senior/calendar/', [CalendarController::class, 'seniorUser'])->name('calendar.seniorUser');
    Route::get('/dashboard/senior/calendar/alljuniorlist', [CalendarController::class, 'allJuniorlist'])->name('calendar.allJuniorlist');
    Route::get('/dashboard/senior/calendar/alladminlist', [CalendarController::class, 'allAdminlist'])->name('calendar.allAdminlist');
    Route::get('/dashboard/senior/calendar/alljunior/{userId}', [CalendarController::class, 'alljuniorUser'])->name('calendar.alljuniorUser');
    Route::get('/dashboard/senior/calendar/allsenior/{userId}', [CalendarController::class, 'allseniorUser'])->name('calendar.allseniorUser');
    Route::get('/dashboard/senior/calendar/allaccountant/{userId}', [CalendarController::class, 'allaccountantUser'])->name('calendar.allaccountantUser');
    Route::get('/dashboard/senior/calendar/alltrainer/{userId}', [CalendarController::class, 'alltrainerUser'])->name('calendar.alltrainerUser');
    Route::get('/dashboard/senior/calendar/alljuniorevents/{userId}', [CalendarController::class, 'getallJuniorEvents'])->name('calendar.allJuniorEvents');
    Route::get('/dashboard/senior/calendar/allseniorevents/{userId}', [CalendarController::class, 'getallSeniorEvents'])->name('calendar.allSeniorEvents');
    Route::get('/dashboard/senior/calendar/allaccountantevents/{userId}', [CalendarController::class, 'getallAccountantEvents'])->name('calendar.allAccountantEvents');
    Route::get('/dashboard/senior/calendar/alltrainerevents/{userId}', [CalendarController::class, 'getallTrainerEvents'])->name('calendar.allTrainerEvents');
    Route::get('/dashboard/senior/calendar/events', [CalendarController::class, 'SeniorEvents'])->name('calendar.seniorEvents');
    Route::post('/dashboard/calendar/update-status', [CalendarController::class, 'updateStatus'])->name('calendar.updateStatus');
    Route::get('/dashboard/calendar/setting', [CalendarController::class, 'setting'])->name('calendar.setting');
    Route::post('/holiday/save', [CalendarController::class, 'saveHoliday'])->name('calendar.holiday');
    Route::post('/holiday/by-month', [CalendarController::class, 'getHolidaysByMonth'])->name('calendar.month');

    Route::get('/dashboard/admin/calendar/', [CalendarController::class, 'adminUser'])->name('calendar.adminUser');
    Route::get('/dashboard/accountant/calendar/', [CalendarController::class, 'accountantUser'])->name('calendar.accountantUser');
    Route::get('/dashboard/trainer/calendar/', [CalendarController::class, 'trainerUser'])->name('calendar.trainerUser');
    Route::get('/dashboard/senior/calendar/allseniorlist', [CalendarController::class, 'allSeniorlist'])->name('calendar.allSeniorlist');
    Route::get('/dashboard/senior/calendar/allaccountantlist', [CalendarController::class, 'allAccountantlist'])->name('calendar.allAccountantlist');
    Route::get('/dashboard/senior/calendar/alltrainerlist', [CalendarController::class, 'allTrainerlist'])->name('calendar.allTrainerlist');

    Route::get('/dashboard/admin/google-sheet', [GoogleSheetController::class, 'admin'])->name('google.sheet.index');
    Route::post('/dashboard/admin/google-sheet/fetch', [GoogleSheetController::class, 'adminfetch'])->name('google.sheet.adminfetch');
    Route::patch('/dashboard/admin/google-sheet/update/{id}', [GoogleSheetController::class, 'adminupdate'])->name('google.sheet.adminupdate');
    Route::post('/dashboard/admin/google-sheet/store', [GoogleSheetController::class, 'adminstore'])->name('google.sheet.adminstore');
    Route::post('/dashboard/admin/google-sheet/adminupdate', [GoogleSheetController::class, 'adminupdate'])->name('adminupdate');
    Route::post('/dashboard/admin/google-sheet/adminstore', [GoogleSheetController::class, 'adminstore'])->name('adminstore');
    Route::get('/dashboard/admin/google-sheet/view-resume/{id}', [GoogleSheetController::class, 'viewadminResume'])->name('view.admin.resume');
    Route::get('/dashboard/admin/google-sheet/download-resume/{id}', [GoogleSheetController::class, 'downloadadminResume'])->name('download.admin.resume');

    Route::get('/dashboard/senior/google-sheet', [GoogleSheetController::class, 'senior'])->name('google.sheet.senior');
    Route::get('/dashboard/senior/google-sheet-follow', [GoogleSheetController::class, 'seniorfollow'])->name('google.sheet.seniorfollow');
    Route::get('/dashboard/career/google-sheet', [GoogleSheetController::class, 'career'])->name('google.sheet.career');
    Route::get('/dashboard/writer/google-sheet', [GoogleSheetController::class, 'writer'])->name('google.sheet.writer');
    Route::get('/dashboard/senior/google-sheet-candm', [GoogleSheetController::class, 'seniorcandm'])->name('google.sheet.seniorcandm');
    Route::get('/dashboard/senior/google-sheet-admincandm', [GoogleSheetController::class, 'senioradmincandm'])->name('google.sheet.senioradmincandm');
    Route::get('/dashboard/senior/google-sheet-mod', [GoogleSheetController::class, 'seniormod'])->name('google.sheet.seniormod');
    Route::get('/dashboard/senior/google-sheet-tra', [GoogleSheetController::class, 'seniortra'])->name('google.sheet.seniortra');
    Route::get('/dashboard/senior/google-sheet-tra-follow', [GoogleSheetController::class, 'seniortrafollow'])->name('google.sheet.seniortrafollow');
    Route::post('/junior/transfers-update', [GoogleSheetController::class, 'juniorupdatetra'])->name('junior.transfers.update');
    Route::post('/junior/rejected-update', [GoogleSheetController::class, 'juniorupdaterej'])->name('junior.rejected.update');
    Route::get('/dashboard/senior/google-sheet-modcandm', [GoogleSheetController::class, 'seniormodcandm'])->name('google.sheet.seniormodcandm');
    Route::get('/dashboard/senior/google-sheet-modcandmfollow', [GoogleSheetController::class, 'seniormodcandmfollow'])->name('google.sheet.seniormodcandmfollow');
    Route::get('/dashboard/senior/google-sheet-paid', [GoogleSheetController::class, 'seniorpaid'])->name('google.sheet.seniorpaid');
    Route::get('/dashboard/senior/google-sheet-con', [GoogleSheetController::class, 'seniorcon'])->name('google.sheet.seniorcon');
    Route::get('/dashboard/accountant/google-sheet-paid', [GoogleSheetController::class, 'accountantpaid'])->name('google.sheet.accountantpaid');
    Route::get('/dashboard/accountant/google-sheet-con', [GoogleSheetController::class, 'accountantcon'])->name('google.sheet.accountantcon');
    Route::get('/dashboard/accountant/google-sheet-ver', [GoogleSheetController::class, 'accountantver'])->name('google.sheet.accountantver');
    Route::post('/dashboard/senior/google-sheet/fetch', [GoogleSheetController::class, 'seniorfetch'])->name('google.sheet.seniorfetch');
    Route::patch('/dashboard/senior/google-sheet/pdfupdate/{id}', [GoogleSheetController::class, 'seniorpdfupdate'])->name('google.sheet.seniorpdfupdate');
    Route::post('/dashboard/senior/google-sheet/pdfstore', [GoogleSheetController::class, 'seniorpdfstore'])->name('google.sheet.seniorpdfstore');
    Route::post('/dashboard/senior/google-sheet/seniorupdate', [GoogleSheetController::class, 'seniorupdate'])->name('seniorupdate');
    Route::post('/dashboard/senior/google-sheet/seniorupdatemod', [GoogleSheetController::class, 'seniorupdatemod'])->name('seniorupdatemod');
    Route::post('/dashboard/senior/google-sheet/seniorupdatecon', [GoogleSheetController::class, 'seniorupdatecon'])->name('seniorupdatecon');
    Route::post('/dashboard/senior/google-sheet/seniorstore', [GoogleSheetController::class, 'seniorstore'])->name('seniorstore');
    Route::post('/dashboard/senior/google-sheet/seniorstoremod', [GoogleSheetController::class, 'seniorstoremod'])->name('seniorstoremod');
    Route::get('/dashboard/senior/google-sheet/view-resume/{id}', [GoogleSheetController::class, 'viewseniorResume'])->name('view.resume');
    Route::get('/dashboard/senior/google-sheet/download-resume/{id}', [GoogleSheetController::class, 'downloadseniorResume'])->name('download.resume');
    Route::get('/dashboard/senior/google-sheet/view-updateresume/{id}', [GoogleSheetController::class, 'viewseniorUpdateResume'])->name('view.updateresume');
    Route::get('/dashboard/senior/google-sheet/download-updateresume/{id}', [GoogleSheetController::class, 'downloadseniorUpdateResume'])->name('download.updateresume');
    Route::get('/dashboard/senior/google-sheet/view-acceptance/{id}', [GoogleSheetController::class, 'viewseniorAcceptance'])->name('view.acceptance');
    Route::get('/dashboard/senior/google-sheet/view-acceptancesign/{id}', [GoogleSheetController::class, 'viewseniorAcceptanceSign'])->name('view.acceptancesign');
    Route::get('/dashboard/senior/google-sheet/download-acceptance/{id}', [GoogleSheetController::class, 'downloadseniorAcceptance'])->name('download.acceptance');
    Route::get('/dashboard/senior/google-sheet/download-acceptancesign/{id}', [GoogleSheetController::class, 'downloadseniorAcceptanceSign'])->name('download.acceptancesign');
    Route::get('/dashboard/senior/google-sheet/view-consultation/{id}', [GoogleSheetController::class, 'viewseniorConsultation'])->name('view.consultation');
    Route::get('/dashboard/senior/google-sheet/download-consultation/{id}', [GoogleSheetController::class, 'downloadseniorconsultation'])->name('download.consultation');
    Route::get('/dashboard/senior/google-sheet/view-delivery/{id}', [GoogleSheetController::class, 'viewseniorDelivery'])->name('view.delivery');
    Route::get('/dashboard/senior/google-sheet/download-delivery/{id}', [GoogleSheetController::class, 'downloadseniorDelivery'])->name('download.delivery');
    Route::get('/dashboard/senior/google-sheet/view-deliverysign/{id}', [GoogleSheetController::class, 'viewseniorDeliverySign'])->name('view.deliverysign');
    Route::get('/dashboard/senior/google-sheet/download-deliverysign/{id}', [GoogleSheetController::class, 'downloadseniorDeliverySign'])->name('download.deliverysign');
    Route::get('/dashboard/senior/google-sheet/view-payment/{id}', [GoogleSheetController::class, 'viewseniorPayment'])->name('view.payment');
    Route::get('/dashboard/senior/google-sheet/view-paymentsign/{id}', [GoogleSheetController::class, 'viewseniorPaymentSign'])->name('view.paymentsign');
    Route::get('/dashboard/senior/google-sheet/download-payment/{id}', [GoogleSheetController::class, 'downloadseniorPayment'])->name('download.payment');
    Route::get('/dashboard/senior/google-sheet/view-audio/{id}', [GoogleSheetController::class, 'viewseniorAudio'])->name('view.audio');
    Route::get('/dashboard/senior/google-sheet/download-audio/{id}', [GoogleSheetController::class, 'downloadseniorAudio'])->name('download.audio');
    Route::get('/dashboard/senior/google-sheet/search-tra', [GoogleSheetController::class, 'seniortraSuggestions'])->name('seniortra.suggestions');
    Route::get('/dashboard/senior/google-sheet/search-seniortrafollow', [GoogleSheetController::class, 'seniortrafollowSuggestions'])->name('seniortrafollow.suggestions');
    Route::get('/dashboard/senior/google-sheet/search-seniorupdatemod', [GoogleSheetController::class, 'seniorupdatemodSuggestions'])->name('seniorupdatemod.suggestions');
    Route::get('/dashboard/senior/google-sheet/search-follow', [GoogleSheetController::class, 'seniorfollowSuggestions'])->name('seniorfollow.suggestions');
    Route::get('/dashboard/senior/google-sheet/search-modcandm', [GoogleSheetController::class, 'seniormodcandmSuggestions'])->name('seniormodcandm.suggestions');
    Route::get('/dashboard/senior/google-sheet/search-modcandmfollow', [GoogleSheetController::class, 'seniormodcandmfollowSuggestions'])->name('seniormodcandmfollow.suggestions');
    Route::get('/dashboard/senior/google-sheet/seniorcandm', [GoogleSheetController::class, 'seniorcandmSuggestions'])->name('seniorcandm.suggestions');
    Route::get('/dashboard/senior/google-sheet/search', [GoogleSheetController::class, 'seniorSuggestions'])->name('senior.suggestions');
    Route::get('/dashboard/senior/google-sheet/search-paid', [GoogleSheetController::class, 'seniorpaidSuggestions'])->name('senior.suggestionspaid');
    Route::get('/dashboard/career/google-sheet/search', [GoogleSheetController::class, 'careerSuggestions'])->name('career.suggestions');
    Route::get('/dashboard/senior/google-sheet/searchmod', [GoogleSheetController::class, 'seniorSuggestionsmod'])->name('senior.suggestionsmod');
    Route::get('/dashboard/accountant/google-sheet/search', [GoogleSheetController::class, 'accountantSuggestions'])->name('accountant.suggestions');
    Route::get('/dashboard/trainer/google-sheet/search', [GoogleSheetController::class, 'trainerSuggestions'])->name('trainer.suggestions');
    Route::get('/dashboard/junior/google-sheet/search', [GoogleSheetController::class, 'juniorSuggestions'])->name('junior.suggestions');
    Route::get('/dashboard/juniorrej/google-sheet/search', [GoogleSheetController::class, 'juniorrejSuggestions'])->name('juniorrej.suggestions');
    Route::get('/dashboard/junior/google-sheet/searchcandm', [GoogleSheetController::class, 'juniorcandmSuggestions'])->name('juniorcandm.suggestions');
    Route::get('/dashboard/junior/google-sheet/searchtra', [GoogleSheetController::class, 'juniortraSuggestions'])->name('juniortra.suggestions');

    Route::get('/dashboard/junior/google-sheet', [GoogleSheetController::class, 'junior'])->name('google.sheet.junior');
    Route::get('/dashboard/junior/google-sheet-rej', [GoogleSheetController::class, 'juniorrej'])->name('google.sheet.juniorrej');
    Route::get('/dashboard/junior/google-sheet-candm', [GoogleSheetController::class, 'juniorcandm'])->name('google.sheet.junior.candm');
    Route::get('/dashboard/junior/google-sheet-tra', [GoogleSheetController::class, 'juniortra'])->name('google.sheet.junior.tra');
    Route::get('/dashboard/junior/google-sheet-modcandm', [GoogleSheetController::class, 'juniormodcandm'])->name('google.sheet.juniormodcandm');
    Route::post('/dashboard/junior/google-sheet-candm-update', [GoogleSheetController::class, 'juniorcandmupdate'])->name('juniorcandmupdate');
    Route::post('/dashboard/senior/google-sheet-candm-update', [GoogleSheetController::class, 'seniorcandmupdate'])->name('seniorcandmupdate');
    Route::post('/dashboard/junior/google-sheet/fetch', [GoogleSheetController::class, 'juniorfetch'])->name('google.sheet.juniorfetch');
    Route::patch('/dashboard/junior/google-sheet/pdfupdate/{id}', [GoogleSheetController::class, 'juniorpdfupdate'])->name('google.sheet.juniorpdfupdate');
    Route::post('/dashboard/junior/google-sheet/pdfstore', [GoogleSheetController::class, 'juniorpdfstore'])->name('google.sheet.juniorpdfstore');
    Route::post('/dashboard/junior/google-sheet/juniorstore', [GoogleSheetController::class, 'juniorstore'])->name('juniorstore');
    Route::post('/dashboard/junior/google-sheet/juniorupdate', [GoogleSheetController::class, 'juniorupdate'])->name('juniorupdate');
    Route::post('/dashboard/junior/google-sheet/juniorupdaterejected', [GoogleSheetController::class, 'juniorupdaterejected'])->name('juniorupdaterejected');
    Route::get('/dashboard/junior/google-sheet/view-resume/{id}', [GoogleSheetController::class, 'viewjuniorResume'])->name('view.resume');
    Route::get('/dashboard/junior/google-sheet/download-resume/{id}', [GoogleSheetController::class, 'downloadjuniorResume'])->name('download.resume');

    Route::get('/dashboard/trainer/google-sheet', [GoogleSheetController::class, 'trainer'])->name('google.sheet.trainer');
    Route::get('/dashboard/trainer/google-sheet-completed', [GoogleSheetController::class, 'trainercompleted'])->name('google.sheet.trainercompleted');
    Route::post('/dashboard/trainer/google-sheet/fetch', [GoogleSheetController::class, 'trainerfetch'])->name('google.sheet.trainerfetch');
    Route::patch('/dashboard/trainer/google-sheet/pdfupdate/{id}', [GoogleSheetController::class, 'trainerpdfupdate'])->name('google.sheet.trainerpdfupdate');
    Route::post('/dashboard/trainer/google-sheet/pdfstore', [GoogleSheetController::class, 'trainerpdfstore'])->name('google.sheet.trainerpdfstore');
    Route::post('/dashboard/trainer/google-sheet/trainerstore', [GoogleSheetController::class, 'trainertore'])->name('trainerstore');
    Route::post('/dashboard/trainer/google-sheet/trainerupdate', [GoogleSheetController::class, 'trainerupdate'])->name('trainerupdate');
    Route::get('/dashboard/trainer/google-sheet/view-resume/{id}', [GoogleSheetController::class, 'viewtrainerResume'])->name('view.resume');
    Route::get('/dashboard/trainer/google-sheet/download-resume/{id}', [GoogleSheetController::class, 'downloadtrainerResume'])->name('download.resume');

    Route::get('/dashboard/accountant/google-sheet', [GoogleSheetController::class, 'accountant'])->name('google.sheet.accountant');
    Route::post('/dashboard/accountant/google-sheet/fetch', [GoogleSheetController::class, 'accountantfetch'])->name('google.sheet.accountantfetch');
    Route::patch('/dashboard/accountant/google-sheet/pdfupdate/{id}', [GoogleSheetController::class, 'accountantpdfupdate'])->name('google.sheet.accountantpdfupdate');
    Route::post('/dashboard/accountant/google-sheet/pdfstore', [GoogleSheetController::class, 'accountantpdfstore'])->name('google.sheet.accountantpdfstore');
    Route::post('/dashboard/accountant/google-sheet/accountantstore', [GoogleSheetController::class, 'accountantstore'])->name('accountantstore');
    Route::post('/dashboard/accountant/google-sheet/accountantupdate', [GoogleSheetController::class, 'accountantupdate'])->name('accountantupdate');
    Route::post('/dashboard/accountant/google-sheet/writterupdate', [GoogleSheetController::class, 'writterupdate'])->name('writterupdate');
    Route::post('/dashboard/accountant/google-sheet/accountantupdatecon', [GoogleSheetController::class, 'accountantupdatecon'])->name('accountantupdatecon');
    Route::post('/dashboard/accountant/google-sheet/accountantupdatever', [GoogleSheetController::class, 'accountantupdatever'])->name('accountantupdatever');
    Route::get('/dashboard/accountant/google-sheet/view-resume/{id}', [GoogleSheetController::class, 'viewaccountantResume'])->name('view.resume');
    Route::get('/dashboard/accountant/google-sheet/download-resume/{id}', [GoogleSheetController::class, 'downloadaccountantResume'])->name('download.resume');
    Route::post('/dashboard/check-email', [GoogleSheetController::class, 'checkEmail'])->name('check.uniqueemail');

    Route::get('/dashboard/accountant/candidate', [CandidateController::class, 'accountant'])->name('candidate.accountant');
    Route::post('/dashboard/accountant/candidate/fetch', [CandidateController::class, 'accountantfetch'])->name('candidate.accountantfetch');
    Route::patch('/dashboard/accountant/candidate/pdfupdate/{id}', [CandidateController::class, 'accountantpdfupdate'])->name('candidate.accountantpdfupdate');
    Route::post('/dashboard/accountant/candidate/pdfstore', [CandidateController::class, 'accountantpdfstore'])->name('candidate.accountantpdfstore');
    Route::post('/candidate/{id}/save-followups', [CandidateDetailsController::class, 'saveFollowups'])->name('candidate.saveFollowups');
    Route::post('/candidate/{id}/save-profile', [CandidateDetailsController::class, 'saveProfile'])->name('candidate.saveProfile');
    Route::post('/candidate/{id}/autosave', [CandidateDetailsController::class, 'autoSave'])->name('candidate.autoSave');
    Route::post('/candidate/{id}/save-edu', [CandidateDetailsController::class, 'saveEdu'])->name('candidate.saveEdu');
    Route::post('/candidate/{id}/save-payment', [CandidateDetailsController::class, 'savePayment'])->name('candidate.savePayment');

    Route::get('/dashboard/admin/call-reports', [CallReportController::class, 'index'])->name('call.reports.index');
    Route::get('/dashboard/junior/call-reports', [CallReportController::class, 'junior'])->name('call.reports.junior');
    Route::get('/dashboard/juniormonthly/call-reports', [CallReportController::class, 'juniormonthly'])->name('call.reports.juniormonthly');
    Route::get('/dashboard/senior/call-reports', [CallReportController::class, 'senior'])->name('call.reports.senior');
    Route::get('/dashboard/seniormonthly/call-reports', [CallReportController::class, 'seniormonthly'])->name('call.reports.seniormonthly');
    Route::get('/dashboard/alljuniorlist/call-reports', [CallReportController::class, 'alljuniorlist'])->name('call.reports.alljuniorlist');
    Route::get('/dashboard/allseniorlist/call-reports', [CallReportController::class, 'allseniorlist'])->name('call.reports.allseniorlist');
    Route::get('/dashboard/allaccountantlist/call-reports', [CallReportController::class, 'allaccountantlist'])->name('call.reports.allaccountantlist');
    Route::get('/dashboard/alltrainerlist/call-reports', [CallReportController::class, 'alltrainerlist'])->name('call.reports.alltrainerlist');
    Route::get('/dashboard/alltrainerlist/call-reports-sender', [CallReportController::class, 'reportsender'])->name('call.reports.sender');
    Route::get('/dashboard/alltrainerlist/call-reports-allreport/{userId}', [CallReportController::class, 'allreport'])->name('call.reports.allreport');
    Route::get('/dashboard/alljuniormonthly/call-reports/{userId}', [CallReportController::class, 'alljuniormonthly'])->name('call.reports.alljuniormonthly');
    Route::get('/dashboard/allseniormonthly/call-reports/{userId}', [CallReportController::class, 'allseniormonthly'])->name('call.reports.allseniormonthly');
    Route::get('/dashboard/alltrainermonthly/call-reports/{userId}', [CallReportController::class, 'alltrainermonthly'])->name('call.reports.alltrainermonthly');
    Route::get('/dashboard/allaccountantmonthly/call-reports/{userId}', [CallReportController::class, 'allaccountantmonthly'])->name('call.reports.allaccountantmonthly');
    Route::get('/dashboard/alljuniordaily/call-reports/{userId}', [CallReportController::class, 'alljuniordaily'])->name('call.reports.alljuniordaily');
    Route::get('/dashboard/alljuniorweekly/call-reports/{userId}', [CallReportController::class, 'alljuniorweekly'])->name('call.reports.alljuniorweekly');
    Route::get('/dashboard/allaccountantdaily/call-reports/{userId}', [CallReportController::class, 'allaccountantdaily'])->name('call.reports.allaccountantdaily');
    Route::get('/dashboard/alltrainerdaily/call-reports/{userId}', [CallReportController::class, 'alltrainerdaily'])->name('call.reports.alltrainerdaily');
    Route::get('/dashboard/allseniordaily/call-reports/{userId}', [CallReportController::class, 'allseniordaily'])->name('call.reports.allseniordaily');
    Route::get('/dashboard/allseniorweekly/call-reports/{userId}', [CallReportController::class, 'allseniorweekly'])->name('call.reports.allseniorweekly');

    Route::match(['get', 'post'], '/timer/update', [DashboardController::class, 'updateTimer'])->name('timer.update');
    Route::get('/dashboard/smtp/add', [DashboardController::class, 'add'])->name('smtp.add');
    Route::get('/dashboard/smtp/edit/{user}', [DashboardController::class, 'edit'])->name('smtp.edit');
    Route::get('/dashboard/smtp/editall', [DashboardController::class, 'editall'])->name('smtp.editall');
    Route::get('/dashboard/target/edit/{user}', [DashboardController::class, 'targetedit'])->name('target.edit');
    Route::get('/dashboard/target/add/{user}', [DashboardController::class, 'targetadd'])->name('target.add');
    Route::post('dashboard/target/save/{user}', [DashboardController::class, 'targetSave'])->name('target.save');
    Route::post('dashboard/target/delete/{user}', [DashboardController::class, 'targetDelete'])->name('target.delete');
    Route::get('/dashboard/target/targetall', [DashboardController::class, 'targetall'])->name('target.all');
    Route::post('/dashboard/upload-generated-pdfs', [DashboardController::class, 'uploadGeneratedPdfs'])->name('upload.generated.pdfs');


    Route::put('/dashboard/smtp/allupdate', [DashboardController::class, 'addupdate'])->name('smtp.addupdate');
    Route::put('/dashboard/smtp/update/{user}', [DashboardController::class, 'update'])->name('smtp.update');
    Route::post('/dashboard/smtp/test/{id}', [DashboardController::class, 'test'])->name('smtp.test');
    Route::post('/dashboard/send-payment-mail/{id}', [DashboardController::class, 'sendPaymentMail'])->name('send.payment.mail');
    Route::get('/dashboard/senior/seniortimer', [TimerController::class, 'seniorTimers'])->name('timer.senior');
    Route::get('/dashboard/senior/allseniortimer', [TimerController::class, 'allseniorTimers'])->name('timer.allsenior');
    Route::get('/timer/all-juniors', [TimerController::class, 'allJuniorTimers'])->name('timer.alljuniors');
    Route::get('/dashboard/junior/juniortimer', [TimerController::class, 'juniorTimers'])->name('timer.junior');
    Route::post('/timer/toggle-button-status', [TimerController::class, 'toggleButtonStatus'])->name('timer.toggleButtonStatus');
    Route::post('/timer/toggle-all-status', [TimerController::class, 'toggleAllStatus'])->name('timer.toggleAllStatus');
    Route::get('/dashboard/admin/timer-settings', [TimerController::class, 'index'])->name('timer.admin');
    Route::post('/timers/work-day', [TimerController::class, 'updateWorkDay'])->name('timer.updateWorkDay');
    Route::post('/timers/base-time', [TimerController::class, 'updateBaseTime'])->name('timer.updateBaseTime');

    Route::get('/timers/latest-pause-types', [DashboardController::class, 'getLatestPauseTypes'])->name('timers.latestPauseTypes');

    Route::get('/dashboard/accountant/pdf/acceptance', [PdfController::class, 'acceptance'])->name('pdf.acceptance');
    Route::get('/dashboard/accountant/pdf/consultation', [PdfController::class, 'consultation'])->name('pdf.consultation');
    Route::get('/dashboard/accountant/pdf/delivery', [PdfController::class, 'delivery'])->name('pdf.delivery');
    Route::get('/dashboard/accountant/pdf/payment', [PdfController::class, 'payment'])->name('pdf.payment');
    Route::get('/dashboard/accountant/pdf/deliveryuk', [PdfController::class, 'deliveryuk'])->name('pdf.deliveryuk');
    Route::get('/dashboard/accountant/pdf/paymentuk', [PdfController::class, 'paymentuk'])->name('pdf.paymentuk');

    Route::get('/dashboard/seniorassociate', [DashboardController::class, 'seniorassociate'])->name('dashboard.seniorassociate');
    Route::get('/dashboard/seniorassociate/google-sheet', [GoogleSheetController::class, 'seniorassociate'])->name('google.sheet.seniorassociate');
    Route::get('/dashboard/seniorassociate/candidate/{userId}', [CandidateDetailsController::class, 'seniorassociate'])->name('all.seniorassociate.candidate');

    Route::get('/dashboard/associate/google-sheet', [GoogleSheetController::class, 'associate'])->name('google.sheet.associate');
    Route::get('/dashboard/associate', [DashboardController::class, 'associate'])->name('dashboard.associate');
    Route::get('/dashboard/associate/candidate/services/{userId}/{forwardedBy}', [CandidateDetailsController::class, 'associateservices'])->name('all.associate.services');
    Route::get('/dashboard/associate/candidate/{userId}/{forwardedBy}', [CandidateDetailsController::class, 'associate'])->name('all.associate.candidate');
    Route::post('/dashboard/associate/candidateadd', [GoogleSheetController::class, 'candidateStore'])->name('all.associate.add');
});

Route::get('/admin/logins', [LoginsController::class, 'index'])->name('logins');
Route::post('/logout-user', [LoginController::class, 'ajaxLogout'])->name('ajax.logout');
Route::post('/login-user', [LoginController::class, 'ajaxLogin'])->name('ajax.login');
Route::post('/logincheckStatus-user', [LoginController::class, 'ajaxCheckStatus'])->name('ajax.logincheckStatus');

Route::get('/template/{id}/edit', [EmailTemplateController::class, 'edit'])->name('template.edit');
Route::put('/email-template/{id}', [EmailTemplateController::class, 'update'])->name('template.update');

Route::get('/', [Controller::class, 'index'])->name('home');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/registersubmit', [RegisterController::class, 'register'])->name('register.submit');
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/loginsubmit', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::post('/resumes/upload/{id}', [ResumeController::class, 'upload'])->name('resumes.upload')->middleware('auth');
Route::patch('/resumes/{id}/status', [ResumeController::class, 'updateStatus'])->name('resumes.updateStatus');
Route::patch('/payment/{id}/status', [PaymentController::class, 'updateStatus'])->name('payment.updateStatus');
Route::patch('/training/{id}/trastatus', [PaymentController::class, 'traupdateStatus'])->name('training.updateStatus');
Route::get('/login-history', [LoginController::class, 'loginHistory'])->name('login.history');


Route::get('api/timer/update', [TimerApiController::class, 'update']);
Route::post('api/timer/update', [TimerApiController::class, 'update']);
