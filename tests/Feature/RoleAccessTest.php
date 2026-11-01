<?php
 
namespace Tests\Feature;
 
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
 
class RoleAccessTest extends TestCase
{
    use DatabaseTransactions;
 
    protected function createUser(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'email_verified_at' => now(),
        ]);
    }
 
    /** @test */
    public function test_pemilik_toko_can_access_allowed_routes_but_blocked_from_restricted_ones()
    {
        $user = $this->createUser('pemilik');
 
        $this->actingAs($user)->get('/dashboard')->assertStatus(200);
        $this->actingAs($user)->get('/penjualan/histori')->assertStatus(200);
        $this->actingAs($user)->get('/pembelian/histori')->assertStatus(200);
        $this->actingAs($user)->get('/laporan/penjualan')->assertStatus(200);
        $this->actingAs($user)->get('/laporan/pembelian')->assertStatus(200);
        $this->actingAs($user)->get('/users')->assertStatus(200);
 
        // Restricted
        $this->actingAs($user)->get('/barang')->assertStatus(403);
        $this->actingAs($user)->get('/penjualan/create')->assertStatus(403);
        $this->actingAs($user)->get('/pembelian/create')->assertStatus(403);
        $this->actingAs($user)->get('/pengeluaran')->assertStatus(403);
        $this->actingAs($user)->get('/kategori')->assertStatus(403);
    }
 
    /** @test */
    public function test_admin_can_access_allowed_routes_but_blocked_from_restricted_ones()
    {
        $user = $this->createUser('admin');
 
        $this->actingAs($user)->get('/dashboard')->assertStatus(200);
        $this->actingAs($user)->get('/kategori')->assertStatus(200);
        $this->actingAs($user)->get('/barang')->assertStatus(200);
        $this->actingAs($user)->get('/penjualan/create')->assertStatus(200);
        $this->actingAs($user)->get('/penjualan/histori')->assertStatus(200);
        $this->actingAs($user)->get('/pembelian/create')->assertStatus(200);
        $this->actingAs($user)->get('/pembelian/histori')->assertStatus(200);
        $this->actingAs($user)->get('/pengeluaran')->assertStatus(200);
 
        // Restricted
        $this->actingAs($user)->get('/users')->assertStatus(403);
        $this->actingAs($user)->get('/laporan/penjualan')->assertStatus(403);
        $this->actingAs($user)->get('/laporan/pembelian')->assertStatus(403);
    }
 
    /** @test */
    public function test_kasir_can_access_allowed_routes_but_blocked_from_restricted_ones_and_dashboard_redirects()
    {
        $user = $this->createUser('kasir');
 
        // Dashboard redirects to penjualan.create
        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('penjualan.create'));
 
        $this->actingAs($user)->get('/penjualan/create')->assertStatus(200);
        $this->actingAs($user)->get('/penjualan/histori')->assertStatus(200);
 
        // Restricted
        $this->actingAs($user)->get('/barang')->assertStatus(403);
        $this->actingAs($user)->get('/pembelian/create')->assertStatus(403);
        $this->actingAs($user)->get('/pengeluaran')->assertStatus(403);
        $this->actingAs($user)->get('/users')->assertStatus(403);
        $this->actingAs($user)->get('/laporan/penjualan')->assertStatus(403);
    }
 
    /** @test */
    public function test_delete_account_route_is_fully_disabled()
    {
        $user = $this->createUser('pemilik');
 
        // Route itself returns 405 (Method Not Allowed) because DELETE route is disabled
        $this->actingAs($user)->delete('/account')->assertStatus(405);
    }
}
