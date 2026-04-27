<?php

namespace Tests\Feature;

use Botble\ACL\Models\User;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class AdminRouteSmokeTest extends TestCase
{
    private function admin(): User
    {
        $user = User::query()->find(1);

        $this->assertNotNull($user, 'Fixture database must contain the system admin user.');

        return $user;
    }

    public function test_key_grimba_admin_get_routes_render_shared_shell(): void
    {
        $routes = [
            '/admin/grimba/cockpit' => 'grimba-cockpit',
            '/admin/grimba/translation' => 'grimba-admin-wayfinder',
            '/admin/grimba/rss-drafts' => 'grimba-admin-wayfinder',
            '/admin/grimba/rss-feeds' => 'grimba-admin-table-responsive',
            '/admin/grimba/newsapi' => 'grimba-admin-wayfinder',
            '/admin/grimba/news-sources' => 'grimba-admin-table-responsive',
            '/admin/grimba/news-sources/triage' => 'grimba-admin-table-responsive',
            '/admin/grimba/story-clusters' => 'grimba-admin-table-responsive',
            '/admin/grimba/coverage-map' => 'grimba-admin-table-responsive',
            '/admin/grimba/subscribers' => 'grimba-admin-table-responsive',
            '/admin/grimba/cookies' => 'grimba-admin-form-section',
        ];

        foreach ($routes as $uri => $marker) {
            $this->actingAs($this->admin())
                ->get($uri)
                ->assertOk()
                ->assertSee($marker, false);
        }
    }

    public function test_admin_entrypoints_do_not_loop_between_stock_admin_and_grimba_cockpit(): void
    {
        $this->get('/admin')
            ->assertRedirect('/admin/login');

        $this->get('/admin/grimba/cockpit')
            ->assertRedirect('/admin/login');

        $this->actingAs($this->admin())
            ->get('/admin')
            ->assertRedirect('/admin/grimba/cockpit');

        $this->actingAs($this->admin())
            ->get('/admin?stock=1')
            ->assertOk();
    }

    public function test_admin_login_lands_on_grimba_cockpit_instead_of_login_form(): void
    {
        $this->admin()->forceFill(['password' => 'password'])->save();

        $this->post('/admin/login', [
            'username' => 'admin',
            'password' => 'password',
            'remember' => '1',
        ])->assertRedirect('/admin/grimba/cockpit');
    }

    public function test_admin_login_discards_stale_login_intended_url(): void
    {
        $this->admin()->forceFill(['password' => 'password'])->save();

        $this
            ->withSession(['url.intended' => url('/admin/login')])
            ->post('/admin/login', [
                'username' => 'admin',
                'password' => 'password',
                'remember' => '1',
            ])
            ->assertRedirect('/admin/grimba/cockpit');
    }

    public function test_admin_login_does_not_inject_debugbar_payload(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertDontSee('browser-logger-active', false)
            ->assertDontSee('/_boost/browser-logs', false)
            ->assertDontSee('phpdebugbar', false)
            ->assertDontSee('/_debugbar/assets', false);
    }
}
