<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->timestamps();
        });

        $dataQuestions = [
            [
                'question' => 'Guru menyampaikan materi pelajaran dengan contoh dalam kehidupan sehari-hari.',
            ],
            [
                'question' => 'Guru memberikan motivasi kepada siswa untuk belajar dengan sungguh-sungguh.',
            ],
            [
                'question' => 'Guru memberikan contoh saat memulai yang berhubungan dengan kehidupan atau permasalahan yang dihadapi siswa.',
            ],
            [
                'question' => 'Guru menjawab pertanyaan dengan jelas.',
            ],
            [
                'question' => 'Guru mengajak siswa berdiskusi tentang pelajaran yang sedang diajarkan.',
            ],
            [
                'question' => 'Guru membimbing kegiatan yang akan dilakukan selama pembelajaran.',
            ],
            [
                'question' => 'Guru menyampaikan materi pelajaran secara menarik dan mudah dimengerti.',
            ],
            [
                'question' => 'Guru memberikan motivasi kepada siswa untuk memahami materi pelajaran dan menerapkannya dalam kehidupan sehari-hari.',
            ],
            [
                'question' => 'Guru mengajar dengan cara yang bervariasi misalnya diskusi, demonstrasi, tanya jawab, ceramah, dll.',
            ],
            [
                'question' => 'Guru berbicara dengan teles ketika menyampaikan materi pelajaran agar dapat dipahami oleh semua siswa.',
            ],
            [
                'question' => 'Guru meminta belajar secara berkelompok.',
            ],
        ];

        DB::table('survey_questions')->insert($dataQuestions);
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_questions');
    }
};
