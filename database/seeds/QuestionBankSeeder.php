<?php

use App\Exceptions\Question\QuestionBankNotFoundException;
use App\Model\QuestionBank;
use Illuminate\Database\Seeder;

class QuestionBankSeeder extends Seeder
{
    public function run()
    {
        try {
            $generalQB = QuestionBank::getQuestionBankBySlug("general");
        } catch (QuestionBankNotFoundException $e) {
            QuestionBank::insert([
                "question_bank_id" => QuestionBank::getNextSequence(),
                "question_bank_name" => "General",
                "question_bank_slug" => QuestionBank::getQuestionBankNameSlug("General"),
                "default" => true,
                "keywords" => [],
                "questions" => [],
                "draft_questions" => [],
                "editor_images" => [],
                "status" => "ACTIVE",
                "created_by" => "ultronlinkstreet",
                "created_at" => time()
            ]);
        } catch (\Exception $e) {
            Log::error("Error message:" . $e->getMessage() . "|File Path:" . $e->getFile() . "|" . $e->getLine());
        }
    }
}