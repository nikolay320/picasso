<?php
class Sabai_Addon_Questions_Helper_SendAnswerNotification extends Sabai_Helper
{
    public function help(Sabai $application, $name, Sabai_Addon_Entity_Entity $answer, $user = null, array $tags = array(), $prefix = 'answer_')
    {
        if (!$question = $application->Content_ParentPost($answer)) {
            return;
        }
        $bundle = $application->Entity_Bundle($answer);
        $tags += $application->Entity_TemplateTags($answer, $prefix) + $application->Entity_TemplateTags($question, 'question_');
        $categories = array();
        foreach ($question->questions_categories as $category) {
            $categories[] = $category->getTitle();
        }
        $tags['{question_categories}'] = implode(', ', $categories);
        $_tags = array();
        foreach ($question->questions_tags as $tag) {
            $_tags[] = $tag->getTitle();
        }
        $tags['{question_tags}'] = implode(', ', $_tags);
        if (!isset($user)) {
            $user = $application->Entity_Author($answer);
        }
        foreach ((array)$name as $notification_name) {
            $application->System_SendEmail($bundle->addon, $notification_name, $tags, $user);
        }
    }
}