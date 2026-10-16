<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use PragmaRX\Google2FA\Google2FA;

use function Pest\Laravel\postJson;

beforeEach(function () {
    Cache::flush(); // Login-Limits nicht über Tests hinweg mitschleppen
    Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
    Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);

    $this->user = User::find(1);
    $this->user->password = 'geheim-12345';
    $this->user->save();
    $this->withHeaders(['company' => $this->user->companies()->first()->id]);
});

function twoFactorAktivieren(User $user): array
{
    Sanctum::actingAs($user, ['*']);
    $secret = postJson('api/v1/me/two-factor/enable')->assertOk()->json('secret');
    $codes = postJson('api/v1/me/two-factor/confirm', [
        'code' => (new Google2FA)->getCurrentOtp($secret),
    ])->assertOk()->json('recovery_codes');

    // Wieder abmelden und den Web-Guard zum Standard machen – wie bei einem
    // echten Login-Versuch, der ohne Sitzung startet
    app('auth')->forgetGuards();
    app('auth')->shouldUse('web');

    return [$secret, $codes];
}

test('2FA lässt sich mit gültigem Code aktivieren', function () {
    [, $codes] = twoFactorAktivieren($this->user);

    expect($codes)->toHaveCount(8);
    expect($this->user->fresh()->hasTwoFactorEnabled())->toBeTrue();
});

test('2FA-Bestätigung mit falschem Code schlägt fehl', function () {
    Sanctum::actingAs($this->user, ['*']);
    postJson('api/v1/me/two-factor/enable')->assertOk();

    postJson('api/v1/me/two-factor/confirm', ['code' => '000000'])->assertStatus(422);
    expect($this->user->fresh()->hasTwoFactorEnabled())->toBeFalse();
});

test('Web-Login verlangt bei aktiver 2FA den zweiten Faktor', function () {
    twoFactorAktivieren($this->user);

    $this->post('login', ['email' => $this->user->email, 'password' => 'geheim-12345'])
        ->assertStatus(423)
        ->assertJson(['two_factor' => true])
        ->assertSessionHas('login.id', $this->user->id);
});

test('2FA-Abfrage akzeptiert gültigen Code und lehnt falschen ab', function () {
    [$secret] = twoFactorAktivieren($this->user);

    $this->withSession(['login.id' => $this->user->id])
        ->postJson('two-factor-challenge', ['code' => '000000'])
        ->assertStatus(422);

    $this->withSession(['login.id' => $this->user->id])
        ->postJson('two-factor-challenge', ['code' => (new Google2FA)->getCurrentOtp($secret)])
        ->assertOk()
        ->assertJson(['success' => true]);
});

test('Recovery-Code funktioniert genau einmal', function () {
    [, $codes] = twoFactorAktivieren($this->user);

    $this->withSession(['login.id' => $this->user->id])
        ->postJson('two-factor-challenge', ['recovery_code' => $codes[0]])
        ->assertOk();

    $this->withSession(['login.id' => $this->user->id])
        ->postJson('two-factor-challenge', ['recovery_code' => $codes[0]])
        ->assertStatus(422);
});

test('Mobile-Token-Login umgeht die 2FA nicht', function () {
    [$secret] = twoFactorAktivieren($this->user);
    $daten = ['username' => $this->user->email, 'password' => 'geheim-12345', 'device_name' => 'test'];

    postJson('api/v1/auth/login', $daten)->assertStatus(422)->assertJsonValidationErrors('code');
    postJson('api/v1/auth/login', $daten + ['code' => '000000'])->assertStatus(422);
    postJson('api/v1/auth/login', $daten + ['code' => (new Google2FA)->getCurrentOtp($secret)])
        ->assertOk()
        ->assertJsonStructure(['token']);
});

test('Mobile-Token-Login ohne 2FA funktioniert weiterhin', function () {
    postJson('api/v1/auth/login', [
        'username' => $this->user->email, 'password' => 'geheim-12345', 'device_name' => 'test',
    ])->assertOk()->assertJsonStructure(['token']);
});

test('Mobile-Token-Login ist gegen Durchprobieren begrenzt', function () {
    $daten = ['username' => $this->user->email, 'password' => 'falsch', 'device_name' => 'test'];

    foreach (range(1, 10) as $i) {
        postJson('api/v1/auth/login', $daten)->assertStatus(422);
    }
    postJson('api/v1/auth/login', $daten)->assertStatus(429);
});

test('2FA-Abfrage ist gegen Durchprobieren der Codes begrenzt', function () {
    twoFactorAktivieren($this->user);

    foreach (range(1, 5) as $i) {
        $this->withSession(['login.id' => $this->user->id])
            ->postJson('two-factor-challenge', ['code' => '000000'])
            ->assertStatus(422);
    }
    $this->withSession(['login.id' => $this->user->id])
        ->postJson('two-factor-challenge', ['code' => '000000'])
        ->assertStatus(429);
});

test('2FA lässt sich nur mit richtigem Passwort abschalten', function () {
    twoFactorAktivieren($this->user);
    Sanctum::actingAs($this->user, ['*']);

    postJson('api/v1/me/two-factor/disable', ['password' => 'falsch'])->assertStatus(422);
    postJson('api/v1/me/two-factor/disable', ['password' => 'geheim-12345'])->assertOk();
    expect($this->user->fresh()->hasTwoFactorEnabled())->toBeFalse();
});
