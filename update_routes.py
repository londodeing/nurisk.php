import re

with open('routes/web.php', 'r') as f:
    content = f.read()

# We want to insert the admin.approval routes near the other admin routes.
# Let's search for "admin.jabatan.index" or "admin.pengguna.index"
marker = "Route::resource('pengguna', \App\Http\Controllers\Web\PenggunaWebController::class);"
new_routes = """Route::resource('pengguna', \App\Http\Controllers\Web\PenggunaWebController::class);
        
        // Approval Akun Registrasi
        Route::prefix('approval')->name('approval.')->group(function() {
            Route::get('/', [\App\Http\Controllers\Web\AdminApprovalController::class, 'index'])->name('index');
            Route::post('/{id}/approve', [\App\Http\Controllers\Web\AdminApprovalController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [\App\Http\Controllers\Web\AdminApprovalController::class, 'reject'])->name('reject');
        });"""

if marker in content:
    content = content.replace(marker, new_routes)
    with open('routes/web.php', 'w') as f:
        f.write(content)
    print("Routes injected successfully")
else:
    print("Marker not found in routes/web.php")
