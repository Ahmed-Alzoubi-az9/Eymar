<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CommunityPost;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityAndAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_members_can_publish_comment_and_toggle_post_likes(): void
    {
        $author = User::factory()->create(['role' => 'customer']);
        $member = User::factory()->create(['role' => 'customer']);

        $this->actingAs($author)->post(route('community.store'), [
            'title' => 'A renovation tip',
            'body' => 'Measure twice before cutting.',
        ])->assertRedirect();
        $post = CommunityPost::query()->firstOrFail();

        $this->actingAs($member)->post(route('community.comments.store', $post), [
            'body' => 'Thank you for sharing.',
        ])->assertRedirect();
        $this->assertSame(1, $author->unreadNotifications()->count());

        $this->post(route('community.likes.toggle', $post))->assertRedirect();
        $this->assertDatabaseHas('community_likes', ['post_id' => $post->id, 'user_id' => $member->id]);

        $this->post(route('community.likes.toggle', $post))->assertRedirect();
        $this->assertDatabaseMissing('community_likes', ['post_id' => $post->id, 'user_id' => $member->id]);
    }

    public function test_staff_can_approve_a_product_but_customers_cannot(): void
    {
        $this->seed();
        $seller = User::factory()->create(['role' => 'company']);
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $category = Category::query()->where('type', 'product')->firstOrFail();
        $product = Product::query()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Concrete blocks',
            'slug' => 'concrete-blocks',
            'description' => 'Solid blocks.',
            'price' => '12.00',
            'stock' => 10,
            'is_active' => false,
        ]);

        $this->actingAs($customer)->patch(route('admin.products.status', $product), [
            'is_active' => '1',
        ])->assertForbidden();
        $this->actingAs($admin)->patch(route('admin.products.status', $product), [
            'is_active' => '1',
        ])->assertRedirect();
        $this->assertTrue($product->fresh()->is_active);
    }

    public function test_suspended_accounts_are_blocked_from_authenticated_pages(): void
    {
        $suspended = User::factory()->create(['role' => 'customer', 'status' => 'suspended']);

        $this->actingAs($suspended)->get(route('dashboard'))->assertForbidden();
    }
}
