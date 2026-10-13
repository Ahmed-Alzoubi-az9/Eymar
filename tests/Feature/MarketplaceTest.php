<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_cannot_create_a_staff_account(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Mallory',
            'email' => 'mallory@example.test',
            'username' => 'mallory',
            'role' => 'admin',
            'phone' => '+962791234567',
            'password' => 'A-strong-password1!',
            'password_confirmation' => 'A-strong-password1!',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'mallory@example.test']);
    }

    public function test_customer_can_register_and_add_a_professional_profile_without_losing_customer_access(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Sami Builder',
            'username' => 'sami-builder',
            'email' => 'sami@example.test',
            'role' => 'customer',
            'phone' => '+962 79 123 4567',
            'password' => 'A-strong-password1!',
            'password_confirmation' => 'A-strong-password1!',
        ])->assertRedirect(route('dashboard'));

        $customer = User::query()->where('email', 'sami@example.test')->firstOrFail();
        $customer->forceFill(['email_verified_at' => now()])->save();
        $this->assertSame('customer', $customer->role);
        $this->assertSame('+962791234567', $customer->phone);
        $this->assertSame('sami-builder', $customer->username);
        $this->actingAs($customer)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee(__('messages.account_settings'))
            ->assertSee(__('messages.personal_details'))
            ->assertSee('data-profile-image-input', false)
            ->assertSee('data-provider-type', false);

        $this->actingAs($customer)->put(route('profile.update'), [
            'name' => $customer->name,
            'username' => $customer->username,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'provider_type' => 'contractor',
            'experience_years' => 8,
            'previous_work' => 'Residential renovations',
            'pricing_basis' => 'hourly',
            'service_rate' => '25.00',
            'is_available' => '1',
        ])->assertRedirect();

        $this->assertSame('customer', $customer->fresh()->role);
        $this->assertDatabaseHas('service_provider_profiles', [
            'user_id' => $customer->id,
            'provider_type' => 'contractor',
            'pricing_basis' => 'hourly',
            'service_rate' => '25.00',
        ]);
        $this->actingAs($customer)->get(route('projects.create'))->assertOk();
    }

    public function test_companies_register_with_supplier_details_and_public_signup_rejects_worker_roles(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Supplier Owner',
            'username' => 'supplier-owner',
            'email' => 'supplier@example.test',
            'role' => 'company',
            'business_name' => 'Amman Materials',
            'company_owner' => 'Supplier Owner',
            'phone' => '0791234567',
            'password' => 'A-strong-password1!',
            'password_confirmation' => 'A-strong-password1!',
        ])->assertRedirect(route('dashboard'));

        $company = User::query()->where('email', 'supplier@example.test')->firstOrFail();
        $company->forceFill(['email_verified_at' => now()])->save();
        $this->assertSame('company', $company->role);
        $this->actingAs($company)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee(__('messages.company_profile'))
            ->assertSee('data-company-logo-input', false);
        Storage::fake('public');
        $this->actingAs($company)->put(route('profile.update'), [
            'name' => $company->name,
            'username' => $company->username,
            'email' => $company->email,
            'phone' => $company->phone,
            'business_name' => 'Amman Building Supplies',
            'company_owner' => 'Supplier Owner',
            'established_date' => '2015-05-20',
            'bio' => 'Building materials from trusted local suppliers.',
            'profile_image' => UploadedFile::fake()->create('owner.png', 100, 'image/png'),
            'company_logo' => UploadedFile::fake()->create('company.png', 100, 'image/png'),
        ])->assertRedirect();
        $company->refresh();
        $this->assertSame('Amman Building Supplies', $company->business_name);
        Storage::disk('public')->assertExists($company->profile_image_path);
        Storage::disk('public')->assertExists($company->company_logo_path);
        $this->actingAs($company)->get(route('seller.products.index'))->assertOk();
        $this->post(route('logout'))->assertRedirect(route('home'));

        $this->post(route('register.store'), [
            'name' => 'Worker',
            'username' => 'worker-user',
            'email' => 'worker@example.test',
            'role' => 'craftsman',
            'phone' => '0791234567',
            'password' => 'A-strong-password1!',
            'password_confirmation' => 'A-strong-password1!',
        ])->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'worker@example.test']);
    }

    public function test_product_catalog_supports_material_equipment_machinery_and_commercial_filters(): void
    {
        $this->seed();
        $seller = User::factory()->create(['role' => 'company', 'status' => 'active']);
        $materials = Category::query()->where('slug', 'product-materials')->firstOrFail();
        $tools = Category::query()->where('slug', 'product-tools')->firstOrFail();
        $material = $this->createProduct($seller, $materials, 'Jordan Cement', '12.50', 10);
        $material->update([
            'manufacturing_company' => 'Jordan Cement Company',
            'manufacturing_country' => 'Jordan',
            'production_date' => '2026-01-01',
            'expiry_date' => '2028-01-01',
            'storage_info' => 'Keep dry.',
            'risk_info' => 'Avoid inhaling dust.',
            'usage_info' => 'Mix with water and aggregate.',
        ]);
        $equipment = $this->createProduct($seller, $tools, 'Tile Cutter', '75.00', 4);
        $equipment->update(['product_type' => 'equipment']);
        $machinery = $this->createProduct($seller, $tools, 'Mobile Crane', '90.00', 3);
        $machinery->update([
            'product_type' => 'machinery',
            'driver_name' => 'Omar Driver',
            'driver_phone' => '+962791234567',
        ]);
        $unavailable = $this->createProduct($seller, $tools, 'Mixer Rental', '80.00', 0);
        $unavailable->update(['product_type' => 'equipment']);

        $this->get(route('products.index', [
            'product_type' => 'machinery',
            'category' => $tools->slug,
            'min_price' => '50.00',
            'max_price' => '100.00',
            'availability' => 'in_stock',
            'min_stock' => 2,
        ]))->assertOk()->assertSee('Mobile Crane')->assertDontSee('Tile Cutter');

        $this->get(route('products.index', [
            'product_type' => 'equipment',
            'availability' => 'out_of_stock',
        ]))->assertOk()->assertSee('Mixer Rental')->assertDontSee('Tile Cutter');

        $this->get(route('products.show', $material))->assertOk()->assertSee('Jordan Cement Company')->assertSee('Keep dry.');
        $this->get(route('products.show', $machinery))->assertOk()->assertSee('Omar Driver')->assertSee('+962791234567');
        $this->get(route('products.show', $unavailable))->assertOk()->assertSee(__('messages.unavailable'));
        $this->get(route('products.index', ['product_type' => 'unknown']))->assertSessionHasErrors('product_type');
    }

    public function test_company_can_list_edit_and_unlist_typed_catalog_items(): void
    {
        $this->seed();
        $company = User::factory()->create(['role' => 'company']);
        $customer = User::factory()->create(['role' => 'customer']);
        $materials = Category::query()->where('slug', 'product-materials')->firstOrFail();

        $this->actingAs($company)->post(route('seller.products.store'), [
            'name' => 'Structural Cement',
            'product_type' => 'material',
            'category_id' => $materials->id,
            'description' => 'High strength cement for structural work.',
            'price' => '18.50',
            'stock' => 40,
            'manufacturing_company' => 'Jordan Cement',
            'manufacturing_country' => 'Jordan',
            'production_date' => '2026-01-01',
            'expiry_date' => '2028-01-01',
            'storage_info' => 'Keep sealed in a dry store.',
            'risk_info' => 'Wear a dust mask.',
            'usage_info' => 'Follow the mix ratio on the package.',
        ])->assertRedirect(route('seller.products.index'));

        $product = Product::query()->where('name', 'Structural Cement')->firstOrFail();
        $this->assertFalse($product->is_active);
        $this->assertSame('material', $product->product_type);
        $this->assertSame('Jordan Cement', $product->manufacturing_company);

        $this->actingAs($company)->put(route('seller.products.update', $product), [
            'name' => 'Mobile Crane',
            'product_type' => 'machinery',
            'category_id' => $materials->id,
            'description' => 'A crane available with an experienced driver.',
            'price' => '300.00',
            'stock' => 2,
            'manufacturing_company' => 'Jordan Machinery',
            'manufacturing_country' => 'Jordan',
            'production_date' => '2026-01-01',
            'expiry_date' => '2028-01-01',
            'driver_name' => 'Omar Driver',
            'driver_phone' => '+962791234567',
        ])->assertRedirect(route('seller.products.index'));

        $product->refresh();
        $this->assertSame('machinery', $product->product_type);
        $this->assertSame('Omar Driver', $product->driver_name);
        $this->assertSame('Jordan Machinery', $product->manufacturing_company);
        $this->assertFalse($product->is_active);

        $this->actingAs($customer)->post(route('seller.products.store'), [
            'name' => 'Unauthorized product',
        ])->assertForbidden();

        $this->actingAs($company)->delete(route('seller.products.destroy', $product))
            ->assertRedirect(route('seller.products.index'));
        $this->assertDatabaseHas('products', ['id' => $product->id, 'is_active' => false, 'stock' => 0]);
    }

    public function test_professional_directory_filters_provider_experience_location_rate_and_availability(): void
    {
        $available = User::factory()->create(['role' => 'customer', 'name' => 'Available Carpenter', 'location' => 'Amman']);
        $available->serviceProviderProfile()->create([
            'provider_type' => 'craftsman',
            'experience_years' => 8,
            'pricing_basis' => 'hourly',
            'service_rate' => '20.00',
            'is_available' => true,
        ]);
        $unavailable = User::factory()->create(['role' => 'customer', 'name' => 'Unavailable Carpenter', 'location' => 'Amman']);
        $unavailable->serviceProviderProfile()->create([
            'provider_type' => 'craftsman',
            'experience_years' => 8,
            'pricing_basis' => 'hourly',
            'service_rate' => '20.00',
            'is_available' => false,
        ]);
        $otherTrade = User::factory()->create(['role' => 'customer', 'name' => 'Available Contractor', 'location' => 'Irbid']);
        $otherTrade->serviceProviderProfile()->create([
            'provider_type' => 'contractor',
            'experience_years' => 2,
            'pricing_basis' => 'unit',
            'service_rate' => '100.00',
            'is_available' => true,
        ]);

        $this->get(route('professionals.index', [
            'location' => 'Amman',
            'provider_type' => 'craftsman',
            'min_experience' => 5,
            'pricing_basis' => 'hourly',
            'min_rate' => '10.00',
            'max_rate' => '30.00',
            'availability' => 'available',
        ]))->assertOk()->assertSee('Available Carpenter')->assertDontSee('Unavailable Carpenter')->assertDontSee('Available Contractor');
    }

    public function test_password_reset_uses_a_single_use_expiring_laravel_reset_token(): void
    {
        Notification::fake();
        $user = User::factory()->create([
            'email' => 'reset@example.test',
            'password' => 'Old-password1!',
        ]);

        $this->get(route('password.request'))->assertOk();
        $this->post(route('password.email'), ['email' => strtoupper($user->email)])
            ->assertSessionHas('status', __('messages.password_reset_if_account_exists'));
        Notification::assertSentTo($user, ResetPassword::class);

        $notification = Notification::sent($user, ResetPassword::class)->first();
        $this->get(route('password.reset', ['token' => $notification->token, 'email' => $user->email]))
            ->assertOk();
        $this->post(route('password.update'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'New-strong-password2!',
            'password_confirmation' => 'New-strong-password2!',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('New-strong-password2!', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_login_redirects_each_account_to_its_role_home(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'password' => 'Valid-password1!']);
        $company = User::factory()->create(['role' => 'company', 'password' => 'Valid-password1!']);
        $admin = User::factory()->create(['role' => 'admin', 'password' => 'Valid-password1!']);

        $this->post(route('login.store'), ['email' => $customer->email, 'password' => 'Valid-password1!'])
            ->assertRedirect(route('home'));
        $this->post(route('logout'));
        $this->post(route('login.store'), ['email' => $company->email, 'password' => 'Valid-password1!'])
            ->assertRedirect(route('products.index'));
        $this->post(route('logout'));
        $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'Valid-password1!'])
            ->assertRedirect(route('admin.users.index'));
    }

    public function test_customer_can_post_a_project_provider_quotes_and_customer_awards_it(): void
    {
        $this->seed();
        $customer = User::factory()->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'customer', 'business_name' => 'North Build']);
        $provider->serviceProviderProfile()->create(['provider_type' => 'contractor']);
        $category = Category::query()->where('type', 'service')->firstOrFail();

        $this->actingAs($customer)->post(route('projects.store'), [
            'category_id' => $category->id,
            'title' => 'Repair the front steps',
            'description' => 'Replace two damaged stone steps.',
            'location' => 'Alexandria',
            'budget_min' => '100.00',
            'budget_max' => '300.00',
        ])->assertRedirect();

        $project = ServiceRequest::query()->firstOrFail();
        $this->assertSame('open', $project->status);
        $this->assertSame(1, $provider->unreadNotifications()->count());

        $this->actingAs($provider)->post(route('projects.quotes.store', $project), [
            'message' => 'I can complete this safely.',
            'amount' => '225.00',
            'estimated_days' => 2,
        ])->assertRedirect();

        $quote = $project->quotes()->firstOrFail();
        $this->assertSame(1, $customer->unreadNotifications()->count());
        $this->actingAs($customer)->get(route('projects.show', $project))->assertOk();

        $this->actingAs($customer)->post(route('projects.quotes.accept', [$project, $quote]))
            ->assertRedirect();

        $this->assertDatabaseHas('service_requests', [
            'id' => $project->id,
            'status' => 'awarded',
            'accepted_provider_id' => $provider->id,
        ]);
        $this->assertDatabaseHas('service_quotes', ['id' => $quote->id, 'status' => 'accepted']);
        $this->assertSame(2, $provider->unreadNotifications()->count());

        $this->actingAs($provider)->patch(route('projects.status', $project), [
            'status' => 'in_progress',
        ])->assertRedirect();
        $this->assertDatabaseHas('service_requests', ['id' => $project->id, 'status' => 'in_progress']);

        $this->actingAs($customer)->patch(route('projects.status', $project), [
            'status' => 'completed',
        ])->assertRedirect();
        $this->assertDatabaseHas('service_requests', ['id' => $project->id, 'status' => 'completed']);
    }

    public function test_project_visibility_is_limited_to_requesters_service_providers_and_admins(): void
    {
        $this->seed();
        $owner = User::factory()->create(['role' => 'customer']);
        $customer = User::factory()->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'customer']);
        $provider->serviceProviderProfile()->create(['provider_type' => 'contractor']);
        $unrelatedProvider = User::factory()->create(['role' => 'customer']);
        $unrelatedProvider->serviceProviderProfile()->create(['provider_type' => 'craftsman']);
        $company = User::factory()->create(['role' => 'company']);
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::query()->where('type', 'service')->firstOrFail();

        $openProject = $owner->customerServiceRequests()->create([
            'category_id' => $category->id,
            'title' => 'Private open renovation',
            'description' => 'Details for the owner and eligible providers.',
            'location' => 'Amman',
            'status' => 'open',
        ]);
        $awardedProject = $owner->customerServiceRequests()->create([
            'category_id' => $category->id,
            'title' => 'Awarded renovation',
            'description' => 'Only the owner and accepted provider can view this.',
            'location' => 'Amman',
            'status' => 'awarded',
            'accepted_provider_id' => $provider->id,
        ]);

        $this->actingAs($customer)->get(route('projects.show', $openProject))->assertNotFound();
        $this->get(route('projects.index'))->assertDontSee($openProject->title);
        $this->get(route('projects.show', $awardedProject))->assertNotFound();

        $this->actingAs($company)->get(route('projects.index'))->assertDontSee($openProject->title);
        $this->get(route('projects.show', $openProject))->assertNotFound();

        $this->actingAs($provider)->get(route('projects.index'))->assertSee($openProject->title);
        $this->get(route('projects.show', $openProject))->assertOk();
        $this->get(route('projects.show', $awardedProject))->assertOk();

        $this->actingAs($unrelatedProvider)->get(route('projects.show', $awardedProject))->assertNotFound();

        $this->actingAs($owner)->get(route('projects.show', $openProject))->assertOk();
        $this->get(route('projects.show', $awardedProject))->assertOk();

        $this->actingAs($admin)->get(route('projects.show', $openProject))->assertOk();
        $this->get(route('projects.show', $awardedProject))->assertOk();
    }

    public function test_cancelling_an_open_project_rejects_pending_quotes_and_notifies_providers(): void
    {
        $this->seed();
        $customer = User::factory()->create(['role' => 'customer']);
        $firstProvider = User::factory()->create(['role' => 'customer']);
        $secondProvider = User::factory()->create(['role' => 'customer']);
        $previousProvider = User::factory()->create(['role' => 'customer']);
        $category = Category::query()->where('type', 'service')->firstOrFail();
        $project = $customer->customerServiceRequests()->create([
            'category_id' => $category->id,
            'title' => 'Kitchen renovation',
            'description' => 'Update the kitchen safely.',
            'location' => 'Amman',
            'status' => 'open',
        ]);
        $firstQuote = $project->quotes()->create([
            'provider_id' => $firstProvider->id,
            'message' => 'I can do this work.',
            'amount' => '1200.00',
            'status' => 'pending',
        ]);
        $secondQuote = $project->quotes()->create([
            'provider_id' => $secondProvider->id,
            'message' => 'I am available for this job.',
            'amount' => '1350.00',
            'status' => 'pending',
        ]);
        $previousQuote = $project->quotes()->create([
            'provider_id' => $previousProvider->id,
            'message' => 'This quote was already declined.',
            'amount' => '1500.00',
            'status' => 'rejected',
        ]);

        $this->actingAs($customer)->patch(route('projects.status', $project), [
            'status' => 'cancelled',
        ])->assertRedirect();

        $this->assertDatabaseHas('service_requests', ['id' => $project->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('service_quotes', ['id' => $firstQuote->id, 'status' => 'rejected']);
        $this->assertDatabaseHas('service_quotes', ['id' => $secondQuote->id, 'status' => 'rejected']);
        $this->assertDatabaseHas('service_quotes', ['id' => $previousQuote->id, 'status' => 'rejected']);
        $this->assertSame(1, $firstProvider->unreadNotifications()->count());
        $this->assertSame(1, $secondProvider->unreadNotifications()->count());
        $this->assertSame(0, $previousProvider->unreadNotifications()->count());

        $notification = $firstProvider->notifications()->firstOrFail();
        $this->assertSame('Project request cancelled', $notification->data['title']);
        $this->assertSame(route('projects.index'), $notification->data['url']);
    }

    public function test_checkout_groups_items_by_seller_and_updates_stock_transactionally(): void
    {
        $this->seed();
        $buyer = User::factory()->create(['role' => 'customer']);
        $firstSeller = User::factory()->create(['role' => 'company']);
        $secondSeller = User::factory()->create(['role' => 'company']);
        $category = Category::query()->where('type', 'product')->firstOrFail();
        $firstProduct = $this->createProduct($firstSeller, $category, 'Cement bag', '10.25', 8);
        $secondProduct = $this->createProduct($secondSeller, $category, 'Copper wire', '3.10', 5);

        $this->actingAs($buyer)->post(route('cart.store', $firstProduct), [
            'quantity' => 2,
            'fulfillment_type' => 'purchase',
        ])->assertRedirect();
        $this->actingAs($buyer)->post(route('cart.store', $secondProduct), [
            'quantity' => 3,
            'fulfillment_type' => 'purchase',
        ])->assertRedirect();
        $this->actingAs($buyer)->post(route('checkout.store'), [
            'shipping_name' => 'Buyer',
            'shipping_phone' => '01000000000',
            'shipping_address' => 'Alexandria, Egypt',
            'notes' => 'Call on arrival.',
            'payment_method' => 'cash_on_delivery',
        ])->assertRedirect(route('orders.index'));

        $orders = Order::query()->with('items')->orderBy('seller_id')->get();
        $this->assertCount(2, $orders);
        $this->assertSame('20.50', $orders[0]->total);
        $this->assertSame('9.30', $orders[1]->total);
        $this->assertSame('cash_on_delivery', $orders[0]->payment_method);
        $this->assertSame('pending', $orders[0]->payment_status);
        $this->actingAs($buyer)->get(route('orders.show', $orders[0]))->assertOk();
        $this->assertSame(6, $firstProduct->fresh()->stock);
        $this->assertSame(2, $secondProduct->fresh()->stock);
        $this->assertDatabaseCount('cart_items', 0);
        $this->assertSame(1, $firstSeller->unreadNotifications()->count());
        $this->assertSame(1, $secondSeller->unreadNotifications()->count());

        $this->actingAs($buyer)->patch(route('orders.status', $orders[0]), ['status' => 'cancelled'])
            ->assertRedirect();
        $this->assertSame(8, $firstProduct->fresh()->stock);
        $this->assertDatabaseHas('orders', ['id' => $orders[0]->id, 'status' => 'cancelled']);

        $this->actingAs($secondSeller)->patch(route('orders.status', $orders[1]), ['status' => 'confirmed'])
            ->assertRedirect();
        $this->actingAs($secondSeller)->patch(route('orders.status', $orders[1]), ['status' => 'shipped'])
            ->assertRedirect();
        $this->actingAs($buyer)->patch(route('orders.status', $orders[1]), ['status' => 'delivered'])
            ->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $orders[1]->id,
            'status' => 'delivered',
            'payment_status' => 'paid',
        ]);
    }

    public function test_rentals_reserve_stock_for_date_ranges_and_charge_cod_by_day(): void
    {
        $this->seed();
        $buyer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'company']);
        $category = Category::query()->where('type', 'product')->firstOrFail();
        $product = $this->createProduct($seller, $category, 'Concrete Mixer', '100.00', 2);
        $product->update([
            'product_type' => 'equipment',
            'is_rentable' => true,
            'rental_rate_per_day' => '15.00',
        ]);

        $this->actingAs($buyer)->post(route('cart.store', $product), [
            'quantity' => 2,
            'fulfillment_type' => 'rental',
            'rental_days' => 3,
            'rental_start_date' => now()->addDays(2)->toDateString(),
        ])->assertRedirect(route('cart.index'));
        $this->actingAs($buyer)->get(route('cart.index'))->assertOk()->assertSee('90.00');
        $this->actingAs($buyer)->post(route('checkout.store'), [
            'shipping_name' => 'Buyer',
            'shipping_phone' => '0791234567',
            'shipping_address' => 'Amman, Jordan',
            'payment_method' => 'cash_on_delivery',
        ])->assertRedirect(route('orders.index'));

        $order = Order::query()->with('items')->firstOrFail();
        $item = $order->items->firstOrFail();
        $this->assertSame('90.00', $order->total);
        $this->assertSame('rental', $item->fulfillment_type);
        $this->assertSame(3, $item->rental_days);
        $this->assertSame(2, $product->fresh()->stock);
        $this->actingAs($buyer)->get(route('orders.show', $order))->assertOk()->assertSee('90.00');

        $overlappingBuyer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($overlappingBuyer)->post(route('cart.store', $product), [
            'quantity' => 1,
            'fulfillment_type' => 'rental',
            'rental_days' => 2,
            'rental_start_date' => now()->addDays(3)->toDateString(),
        ])->assertRedirect(route('cart.index'));
        $this->actingAs($overlappingBuyer)->post(route('checkout.store'), [
            'shipping_name' => 'Another buyer',
            'shipping_phone' => '0791234567',
            'shipping_address' => 'Amman, Jordan',
            'payment_method' => 'cash_on_delivery',
        ])->assertSessionHasErrors('cart');

        $this->actingAs($buyer)->patch(route('orders.status', $order), ['status' => 'cancelled'])
            ->assertRedirect();
        $this->assertSame(2, $product->fresh()->stock);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);

        $this->actingAs($overlappingBuyer)->post(route('checkout.store'), [
            'shipping_name' => 'Another buyer',
            'shipping_phone' => '0791234567',
            'shipping_address' => 'Amman, Jordan',
            'payment_method' => 'cash_on_delivery',
        ])->assertRedirect(route('orders.index'));
        $this->assertDatabaseCount('orders', 2);
    }

    public function test_customer_cannot_manage_products_or_access_staff_routes(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)
            ->get(route('seller.products.index'))
            ->assertForbidden();
        $this->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_customer_provider_and_staff_pages_render(): void
    {
        $this->seed();
        $customer = User::factory()->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'company']);
        $admin = User::factory()->create(['role' => 'admin']);
        $serviceCategory = Category::query()->where('type', 'service')->firstOrFail();
        $productCategory = Category::query()->where('type', 'product')->firstOrFail();
        $product = $this->createProduct($provider, $productCategory, 'Cement bag', '10.25', 8);
        $project = $customer->customerServiceRequests()->create([
            'category_id' => $serviceCategory->id,
            'title' => 'Repair a wall',
            'description' => 'Repair a cracked wall in the kitchen.',
            'location' => 'Alexandria',
            'status' => 'open',
        ]);
        $post = $customer->communityPosts()->create([
            'title' => 'Renovation advice',
            'body' => 'Plan the work before buying materials.',
        ]);

        auth()->logout();
        $this->get(route('professionals.index'))->assertOk();
        $this->get(route('products.index'))->assertOk();
        $this->get(route('products.show', $product))->assertOk();
        $this->get(route('community.index'))->assertOk();
        $this->get(route('login'))->assertOk();
        $this->get(route('register'))->assertOk();

        $this->actingAs($customer);
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('profile.edit'))->assertOk();
        $this->get(route('cart.index'))->assertOk();
        $this->get(route('projects.index'))->assertOk();
        $this->get(route('projects.create'))->assertOk();
        $this->get(route('projects.show', $project))->assertOk();
        $this->get(route('community.index'))->assertOk()->assertSee($post->title);
        $this->get(route('notifications.index'))->assertOk();
        $this->get(route('orders.index'))->assertOk();

        $this->actingAs($provider);
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('seller.products.index'))->assertOk();
        $this->get(route('seller.products.create'))->assertOk();
        $this->get(route('projects.index'))->assertOk();

        $this->actingAs($admin);
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.users.index'))->assertOk();
        $this->get(route('admin.products.index'))->assertOk();
        $this->get(route('admin.orders.index'))->assertOk();
    }

    private function createProduct(User $seller, Category $category, string $name, string $price, int $stock): Product
    {
        return Product::query()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)),
            'description' => $name.' for building work.',
            'price' => $price,
            'stock' => $stock,
            'is_active' => true,
            'product_type' => 'material',
        ]);
    }
}
