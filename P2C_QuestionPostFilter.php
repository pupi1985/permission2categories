<?php

class P2C_QuestionPostFilter
{
    public function filter_question(&$question, &$errors, $oldQuestion)
    {
        if (!isset($question['categoryid'])) {
            return;
        }

        $categoryId = (int)$question['categoryid'];

        $p2c = qa_load_module('process', 'Permissions2Categories');

        $userLevel = qa_user_level_for_categories(array($categoryId));

        if ($p2c->has_permit($categoryId, qa_get_logged_in_userid(), $userLevel)) {
            return;
        }

        $errors['categoryid'] = qa_lang_html('question/category_ask_not_allowed');
    }
}
