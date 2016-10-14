<?php
class Sabai_Addon_Questions_Helper_SendQuestionNotification extends Sabai_Helper
{
    public function help(Sabai $application, $name, Sabai_Addon_Entity_Entity $question, $user = null, array $tags = array(), $prefix = 'question_')
    {
        $bundle = $application->Entity_Bundle($question);
        $tags += $application->Entity_TemplateTags($question, $prefix);
        $categories = array();
        foreach ($question->questions_categories as $category) {
            $categories[] = $category->getTitle();
        }
        $tags['{' . $prefix . 'categories}'] = implode(', ', $categories);
        $_tags = array();
        foreach ($question->questions_tags as $tag) {
            $_tags[] = $tag->getTitle();
        }
        $tags['{' . $prefix . 'tags}'] = implode(', ', $_tags);
        if (!isset($user)) {
            $user = $application->Entity_Author($question);
        }
        foreach ((array)$name as $notification_name) {
            $application->System_SendEmail($bundle->addon, $notification_name, $tags, $user);
        }
    }
}