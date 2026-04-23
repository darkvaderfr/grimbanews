<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('story_clusters', function (Blueprint $table) {
            $table->id();
            $table->string('topic', 200);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Back-fill clusters that already exist via posts.story_cluster_id.
        $now = now();
        $existing = DB::table('posts')
            ->select('story_cluster_id')
            ->whereNotNull('story_cluster_id')
            ->groupBy('story_cluster_id')
            ->pluck('story_cluster_id');

        $labels = [
            1001 => 'Réforme des retraites 2026',
            1002 => "Accord climat de l'Union européenne",
            1003 => 'Sahel — retrait des forces françaises',
        ];

        foreach ($existing as $id) {
            DB::table('story_clusters')->insert([
                'id'         => $id,
                'topic'      => $labels[$id] ?? "Dossier #{$id}",
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('story_clusters');
    }
};
