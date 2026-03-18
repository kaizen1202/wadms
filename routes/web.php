<?php

use App\Http\Controllers\ADMIN\AccreditationProgramController;
use App\Http\Controllers\ADMIN\AccreditationController;
use App\Http\Controllers\ADMIN\AssignmentController;
use App\Http\Controllers\ADMIN\ParameterController;
use App\Http\Controllers\RoleRequestController;
use App\Http\Controllers\ADMIN\AdminAcreditationController;
use App\Http\Controllers\ADMIN\AdminTaskForceController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AccreditationEvaluationController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SwitchRoleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile/details', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware('auth')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index'])
        ->name('users.index');
    Route::get('/users/data', [AdminUserController::class, 'data'])
        ->name('users.data');
    Route::post('/users/{id}/verify', [AdminUserController::class, 'verify'])
     ->name('users.verify');
    Route::patch('/users/{id}/suspend', [AdminUserController::class, 'suspend']);
    Route::get('/task-force', [AdminTaskForceController::class, 'index'])
        ->name('users.taskforce.index');
    Route::get('/task-force/data', [AdminTaskForceController::class, 'data'])
        ->name('taskforce.data');
    Route::get('/taskforce/view/{id}', [AdminTaskForceController::class, 'viewTaskForce'])
        ->name('taskforce.view');


    Route::get('/accreditation', [AdminAcreditationController::class, 'index'])
        ->name('admin.accreditation.index');
    Route::post('/admin/accreditations', [AdminAcreditationController::class, 'store'])
        ->name('admin.accreditations.store');
    Route::get('/admin/accreditations/data', [AdminAcreditationController::class, 'getAccreditations'])
        ->name('admin.accreditations.data');
    Route::post(
        '/admin/accreditations/add-level-programs',
        [AdminAcreditationController::class, 'addLevelWithPrograms']
    )->name('admin.accreditations.addLevelPrograms');
    Route::post(
        '/admin/accreditations/add-program',
        [AdminAcreditationController::class, 'addProgramOnly']
    )->name('admin.accreditations.addProgram');

    // Info, Level, Program Mapping Routes
    Route::post(
        '/admin/accreditations/programs',
        [AccreditationProgramController::class, 'store']
    )->name('admin.accreditations.program.store');
    Route::patch(
        '/admin/accreditations/program/{mapping}',
        [AccreditationProgramController::class, 'update']
    )->name('admin.accreditations.program.update');

    Route::delete(
        '/admin/accreditations/program/{mapping}',
        [AccreditationProgramController::class, 'destroy']
    )->name('admin.accreditations.program.destroy');
    // End

    Route::get(
        '/admin/accreditations/{infoId}/level/{levelId}/program/{programName}',
        [AdminAcreditationController::class, 'showProgram']
    )->name('admin.accreditations.program');
    Route::post('/programs/{program}/areas/save', [AdminAcreditationController::class, 'saveAreas'])
        ->name('programs.areas.save');
    Route::get(
        '/admin/accreditation/{infoId}/{levelId}/{programId}/areas/{programAreaId}/parameters',
        [AdminAcreditationController::class, 'showParameters']
    )->name('program.areas.parameters');
    Route::post('/program-area/{areaId}/parameters', action: [AdminAcreditationController::class, 'storeParameters'])
        ->name('program-area.parameters.store');

    Route::get(
        '/sub-parameter/{subParameter}/uploads/{infoId}/{levelId}/{programId}/{programAreaId}',
        [AdminAcreditationController::class, 'subParameterUploads']
    )->name('subparam.uploads.index');

    Route::post(
        '/sub-parameter/{subParameter}/uploads/{infoId}/{levelId}/{programId}/{programAreaId}',
        [AdminAcreditationController::class, 'storeSubParameterUploads']
    )->name('subparam.uploads.store');

    Route::delete(
        '/subparam/uploads/{upload}',
        [AdminAcreditationController::class, 'destroySubParameterUpload']
    )->name('subparam.uploads.destroy');


    Route::get(
        '/admin/accreditations/{id}/edit',
        [AdminAcreditationController::class, 'edit']
    )->name('accreditation.edit');

    Route::get(
        '/admin/accreditations/{id}',
        [AdminAcreditationController::class, 'show']
    )->name('accreditation.show');

    Route::put(
        '/admin/accreditations/{id}',
        [AdminAcreditationController::class, 'update']
    )->name('admin.accreditations.update');


    //INTERNAL ACCESSOR
    
    Route::get(
        '/internal-assessor',
        [AdminAcreditationController::class, 'indexInternalAccessor']
    )->name('internal-accessor.index');

    
    Route::get(
        '/internal-assessor/{accreditation}/{level}/{program}/areas',
        [AdminAcreditationController::class, 'showProgramAreas']
        )->name('internal.accessor.program.areas');
        
        
    Route::get(
        '/evaluations',
        [AccreditationEvaluationController::class, 'index']
        )->name('program.areas.evaluations');
        
    Route::get(
        '/program-areas/{infoId}/{levelId}/{programId}/{programAreaId}/evaluation',
        [AdminAcreditationController::class, 'showAreaEvaluation']
        )->name('program.areas.evaluation');

    Route::get(
        '/evaluations/{evaluation}/area/{area}/summary',
        [AccreditationEvaluationController::class, 'show']
    )->name('program.areas.evaluations.summary');
        
    Route::post(
        '/accreditation-evaluations',
        [AccreditationEvaluationController::class, 'store']
    )->name('accreditation-evaluations.store');

    Route::post(
        '/admin/evaluations/{infoId}/{levelId}/{programId}/{programAreaId}',
        [AccreditationController::class, 'store']
    )->name('area.evaluations.store');

    Route::patch(
        '/evaluations/{evaluation}/finalize',
        [AccreditationEvaluationController::class, 'markAsFinal']
    )->name('evaluations.finalize');

    Route::post(
        '/internal/final-verdict',
        [AccreditationController::class, 'storeFinalVerdict']
    )->name('internal.final.verdict.store');

    // FINAL VERDICT
    Route::get('/archive', [ArchiveController::class, 'index'])
        ->name('archive.index');

    // Completed accreditations
    Route::get('/completed', [ArchiveController::class, 'completed'])
        ->name('archive.completed');

    // 🗑 Deleted / Withdrawn accreditations
    Route::get('/deleted', [ArchiveController::class, 'deleted'])
        ->name('archive.deleted');
});

// Route for role requests
Route::middleware(['auth'])->group(function () {

    // View pending requests (for approvers)
    Route::get('/role-requests', [RoleRequestController::class, 'index'])
        ->name('role-requests.index');

    // Submit a new role request (by logged-in user)
    Route::post('/role-requests', [RoleRequestController::class, 'store'])
        ->name('role-requests.store');
    
    // Data used for AJAX
    Route::get('/data', [RoleRequestController::class, 'data'])->name('role-requests.data');

    // Approve a request (for approvers)
    Route::post('/role-requests/{roleRequest}/approve', [RoleRequestController::class, 'approve'])
        ->name('role-requests.approve');

    // Reject a request (for approvers)
    Route::post('/role-requests/{roleRequest}/reject', [RoleRequestController::class, 'reject'])
        ->name('role-requests.reject');
});

// Route for switching role
Route::middleware(['auth'])->group(function () {
    Route::post('/switch-role', [SwitchRoleController::class, 'switch'])
    ->name('switch.role')
    ->middleware('auth');
});

// Route for updating and deleting parameters and sub-parameters
Route::middleware(['auth'])->group(function () {
    Route::patch('/parameters/bulk-update', [ParameterController::class, 'bulkUpdate'])
        ->name('parameters.bulk-update');

    Route::patch('/subparameters/{subParameter}', [ParameterController::class, 'updateSubParameter'])
        ->name('subparameters.update');

        
    Route::delete('/parameters/bulk-delete', [ParameterController::class, 'bulkDelete'])
    ->name('parameters.bulk-delete');
    
    Route::delete('/subparameters/{subParameter}', [ParameterController::class, 'deleteSubParameter'])
        ->name('subparameters.delete');

});

Route::middleware(['auth'])->group(function () {
    Route::post(
        '/admin/areas/assign-users',
        [AdminAcreditationController::class, 'assignUsersToArea']
    )->name('areas.assign.users');
    
    Route::delete(
        'assignments/unassign/{assignment}',
        [AssignmentController::class, 'destroy']
    )->name('assignments.unassign');
});

// Route for global search
Route::get('/global-search', [SearchController::class, 'global'])
    ->name('global.search')
    ->middleware('auth');

require __DIR__ . '/auth.php';
