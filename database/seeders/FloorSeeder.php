<?php

namespace Database\Seeders;

use App\Models\Floor;
use App\Models\RestaurantTable;
use App\Models\Shop;
use Illuminate\Database\Seeder;

class FloorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating floors and tables...');

        // Get the first shop (Restaurant) or create default
        $shop = Shop::where('name', 'Restaurant')->first()
            ?? Shop::first()
            ?? Shop::factory()->create(['name' => 'Main Restaurant']);

        // Create standard floors for restaurant
        $floors = [
            [
                'name' => 'Ground Floor',
                'description' => 'Main dining area with standard seating',
                'sort_order' => 1,
                'tables' => [
                    ['number' => 'G01', 'capacity' => 4, 'x' => 100, 'y' => 100],
                    ['number' => 'G02', 'capacity' => 4, 'x' => 200, 'y' => 100],
                    ['number' => 'G03', 'capacity' => 6, 'x' => 300, 'y' => 100],
                    ['number' => 'G04', 'capacity' => 2, 'x' => 100, 'y' => 200],
                    ['number' => 'G05', 'capacity' => 4, 'x' => 200, 'y' => 200],
                    ['number' => 'G06', 'capacity' => 8, 'x' => 300, 'y' => 200],
                    ['number' => 'G07', 'capacity' => 4, 'x' => 100, 'y' => 300],
                    ['number' => 'G08', 'capacity' => 4, 'x' => 200, 'y' => 300],
                ]
            ],
            [
                'name' => 'First Floor',
                'description' => 'Premium dining with window views',
                'sort_order' => 2,
                'tables' => [
                    ['number' => 'F01', 'capacity' => 4, 'x' => 100, 'y' => 100],
                    ['number' => 'F02', 'capacity' => 4, 'x' => 200, 'y' => 100],
                    ['number' => 'F03', 'capacity' => 6, 'x' => 300, 'y' => 100],
                    ['number' => 'F04', 'capacity' => 2, 'x' => 100, 'y' => 200],
                    ['number' => 'F05', 'capacity' => 4, 'x' => 200, 'y' => 200],
                    ['number' => 'F06', 'capacity' => 8, 'x' => 300, 'y' => 200],
                ]
            ],
            [
                'name' => 'Terrace',
                'description' => 'Outdoor seating area',
                'sort_order' => 3,
                'tables' => [
                    ['number' => 'T01', 'capacity' => 4, 'x' => 100, 'y' => 100],
                    ['number' => 'T02', 'capacity' => 4, 'x' => 200, 'y' => 100],
                    ['number' => 'T03', 'capacity' => 5, 'x' => 300, 'y' => 100],
                    ['number' => 'T04', 'capacity' => 2, 'x' => 100, 'y' => 200],
                    ['number' => 'T05', 'capacity' => 4, 'x' => 200, 'y' => 200],
                ]
            ],
            [
                'name' => 'VIP Section',
                'description' => 'Private dining area',
                'sort_order' => 4,
                'tables' => [
                    ['number' => 'V01', 'capacity' => 10, 'name' => 'VIP Room 1', 'x' => 100, 'y' => 100],
                    ['number' => 'V02', 'capacity' => 12, 'name' => 'VIP Room 2', 'x' => 250, 'y' => 100],
                    ['number' => 'V03', 'capacity' => 8, 'name' => 'VIP Room 3', 'x' => 400, 'y' => 100],
                ]
            ],
        ];

        foreach ($floors as $floorData) {
            $tables = $floorData['tables'];
            unset($floorData['tables']);

            // Create floor
            $floor = Floor::updateOrCreate(
                [
                    'shop_id' => $shop->id,
                    'name' => $floorData['name']
                ],
                array_merge($floorData, [
                    'shop_id' => $shop->id,
                    'is_active' => true,
                    'layout_config' => [
                        'width' => 800,
                        'height' => 600,
                        'background' => 'default',
                    ],
                ])
            );

            // Create tables for this floor
            foreach ($tables as $tableData) {
                RestaurantTable::updateOrCreate(
                    [
                        'floor_id' => $floor->id,
                        'table_number' => $tableData['number']
                    ],
                    [
                        'floor_id' => $floor->id,
                        'table_number' => $tableData['number'],
                        'name' => $tableData['name'] ?? null,
                        'capacity' => $tableData['capacity'],
                        'status' => 'available',
                        'position_x' => $tableData['x'],
                        'position_y' => $tableData['y'],
                        'width' => 80,
                        'height' => 80,
                        'shape' => 'rectangle',
                        'is_active' => true,
                    ]
                );
            }

            $this->command->info("  Created floor: {$floor->name} with " . count($tables) . " tables");
        }

        $totalFloors = Floor::count();
        $totalTables = RestaurantTable::count();
        $this->command->info("✓ Floor seeding complete: {$totalFloors} floors, {$totalTables} tables");
    }
}
