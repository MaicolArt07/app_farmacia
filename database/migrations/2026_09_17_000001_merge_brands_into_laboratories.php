<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('brands')) {
            return;
        }

        $brands = DB::table('brands')->get();
        $brandToLaboratory = [];

        foreach ($brands as $brand) {
            $laboratory = DB::table('laboratories')->where('name', $brand->name)->first();

            if ($laboratory) {
                $laboratoryId = $laboratory->id;
            } else {
                $laboratoryId = DB::table('laboratories')->insertGetId([
                    'name' => $brand->name,
                    'description' => $brand->description,
                    'state' => $brand->state,
                    'created_at' => $brand->created_at,
                    'updated_at' => $brand->updated_at,
                ]);
            }

            $brandToLaboratory[$brand->id] = $laboratoryId;
        }

        if (Schema::hasColumn('products', 'id_brand')) {
            $products = DB::table('products')->whereNotNull('id_brand')->get(['id', 'id_brand', 'id_laboratory']);

            foreach ($products as $product) {
                if ($product->id_laboratory !== null) {
                    continue;
                }

                $mappedLaboratoryId = $brandToLaboratory[$product->id_brand] ?? null;

                if ($mappedLaboratoryId !== null) {
                    DB::table('products')->where('id', $product->id)->update(['id_laboratory' => $mappedLaboratoryId]);
                }
            }

            Schema::table('products', function (Blueprint $table) {
                $table->dropForeign(['id_brand']);
                $table->dropColumn('id_brand');
            });
        }

        Schema::dropIfExists('brands');

        DB::table('permissions')->where('name', 'Ver Marcas')->delete();
    }

    public function down(): void
    {
        if (!Schema::hasTable('brands')) {
            Schema::create('brands', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150);
                $table->string('description', 255)->nullable();
                $table->boolean('state')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasColumn('products', 'id_brand')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('id_brand')->nullable()->constrained('brands')->restrictOnDelete();
            });
        }
    }
};
