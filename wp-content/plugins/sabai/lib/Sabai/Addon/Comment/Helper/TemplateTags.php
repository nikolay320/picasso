<?php
class Sabai_Addon_Comment_Helper_TemplateTags extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Comment_Model_Post $comment, $prefix = 'comment_')
    {
        $author = $comment->User;
        return array(
            '{' . $prefix . 'id}' => $comment->id,
            '{' . $prefix . 'author_name}' => $author->name,
            '{' . $prefix . 'author_email}' => $author->email,
            '{' . $prefix . 'date}' => $application->Date($comment->published_at),
            '{' . $prefix . 'summary}' => $application->Summarize($comment->body_html, 100),
        );
    }
}