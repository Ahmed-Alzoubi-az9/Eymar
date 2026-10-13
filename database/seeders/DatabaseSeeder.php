<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'service' => [
                'Construction', 'Home repairs', 'Electrical', 'Plumbing',
                'Interior finishing', 'Business services',
            ],
            'product' => [
                'Materials', 'Equipment', 'Machinery', 'Building materials', 'Tools', 'Electrical supplies', 'Plumbing supplies',
                'Paint and finishing',
            ],
        ];

        $arabicNames = [
            'Construction' => 'الإنشاءات',
            'Home repairs' => 'إصلاحات المنزل',
            'Electrical' => 'الكهرباء',
            'Plumbing' => 'السباكة',
            'Interior finishing' => 'التشطيبات الداخلية',
            'Business services' => 'خدمات الشركات',
            'Building materials' => 'مواد البناء',
            'Materials' => 'المواد',
            'Equipment' => 'المعدات',
            'Machinery' => 'الآليات',
            'Tools' => 'الأدوات',
            'Electrical supplies' => 'مستلزمات الكهرباء',
            'Plumbing supplies' => 'مستلزمات السباكة',
            'Paint and finishing' => 'الدهانات والتشطيبات',
        ];

        foreach ($categories as $type => $names) {
            foreach ($names as $name) {
                Category::query()->firstOrCreate(
                    ['slug' => Str::slug($type.'-'.$name)],
                    ['type' => $type, 'name' => $name, 'name_ar' => $arabicNames[$name]],
                );
            }
        }
    }
}
