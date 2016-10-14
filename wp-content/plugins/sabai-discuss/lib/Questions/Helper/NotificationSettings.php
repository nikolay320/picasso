<?php
class Sabai_Addon_Questions_Helper_NotificationSettings extends Sabai_Helper
{
    public function help(Sabai $application)
    {
        return array(
            'question_answered' => array(
                'type' => 'user',
                'has_guest_author' => true,
                'title' => __('Question Answered Notification Email', 'sabai-discuss'),
                'description' => __('If enabled, a notification email is sent to the author of a question when an answer is posted to the question.', 'sabai-discuss'),
                'tags' => array_merge($this->_getAnswerTags(), $this->_getQuestionTags()),
                'enable' => true,
                'email' => array(
                    'subject' => __('[{site_name}] Your question has been answered', 'sabai-discuss'),
                    'body' => __('Hi {recipient_name},
                
A new answer has been posted to your question "{question_title}".

Answer by {answer_author_name}:

------------------------------------
{answer_summary}
------------------------------------

You can view the answer at {answer_url}.

Regards,
{site_name}
{site_url}', 'sabai-discuss'),
                ),
            ),
            'answer_accepted' => array(
                'type' => 'user',
                'has_guest_author' => true,
                'title' => __('Answer Accepted Notification Email', 'sabai-discuss'),
                'description' => __('If enabled, a notification email is sent to the author of an answer when the answer is accepted by the question author.', 'sabai-discuss'),
                'tags' => array_merge(array('{acceptance_date}'), $this->_getAnswerTags(), $this->_getQuestionTags()),
                'enable' => true,
                'email' => array(
                    'subject' => __('[{site_name}] Your answer has been accepted', 'sabai-discuss'),
                    'body' => __('Hi {recipient_name},
                
Your answer to the question "{question_title}" has been accepted by the question owner on {acceptance_date}.

You can view the answer at {answer_url}.

Regards,
{site_name}
{site_url}', 'sabai-discuss')
                ),
            ),  
            'comment_posted' => array(
                'type' => 'user',
                'has_guest_author' => true,
                'title' => __('Comment Posted Notification Email', 'sabai-discuss'),
                'description' => __('If enabled, a notification email is sent to the content author when a comment is submitted for the content.', 'sabai-discuss'),
                'tags' => array_merge($this->_getCommentTags(), $this->_getContentTags()),
                'enable' => true,
                'email' => array(
                    'subject' => __('[{site_name}] You have a new comment', 'sabai-discuss'),
                    'body' => __('Hi {recipient_name},
                
A comment has been added to your post "{content_title}".

Comment by {comment_author_name}:

------------------------------------
{comment_summary}
------------------------------------

You can view the comment at {content_url}.

Regards,
{site_name}
{site_url}', 'sabai-discuss')
                ),
            ),
            'content_published' => array(
                'type' => 'user',
                'has_guest_author' => true,
                'title' => __('Published Post Notification Email', 'sabai-discuss'),
                'description' => __('If enabled, a notification email is sent to the author of a content when the content is approved and published by an administrator.', 'sabai-discuss'),
                'tags' => $this->_getContentTags(),
                'enable' => true,
                'email' => array(
                    'subject' => __('[{site_name}] Your post has been published', 'sabai-discuss'),
                    'body' => __('Hi {recipient_name},
                
Your post "{content_title}" has been approved and is now published.

You can view your post at {content_url}.

Regards,
{site_name}
{site_url}', 'sabai-discuss')
                ),
            ),
            'question_posted' => array(
                'type' => 'roles',
                'title' => __('Question Posted Notification Email', 'sabai-discuss'),
                'description' => __('If enabled, a notification email is sent to users of selected roles whenever a question is posted.', 'sabai-discuss'),
                'tags' => $this->_getQuestionTags(),
                'enable' => false,
                'email' => array(
                    'subject' => __('[{site_name}] A new question (ID: {question_id}) has been posted', 'sabai-discuss'),
                    'body' => __('Hi {recipient_name},
                
A new question titled "{question_title}" has been posted on {question_date}.

Posted by {question_author_name} ({question_author_email}):

------------------------------------
{question_summary}
------------------------------------

You can view the question at {question_url}.

Regards,
{site_name}
{site_url}', 'sabai-discuss'),
                ),
            ),
            'answer_posted' => array(
                'type' => 'roles',
                'title' => __('Answer Posted Notification Email', 'sabai-discuss'),
                'description' => __('If enabled, a notification email is sent to users of selected roles whenever an answer is posted.', 'sabai-discuss'),
                'tags' => array_merge($this->_getAnswerTags(), $this->_getQuestionTags()),
                'enable' => false,
                'email' => array(
                    'subject' => __('[{site_name}] A new answer (ID: {answer_id}) has been posted', 'sabai-discuss'),
                    'body' => __('Hi {recipient_name},
                
A new answer to the question "{question_title}" has been posted on {answer_date}.

Posted by {answer_author_name} ({answer_author_email}):

------------------------------------
{answer_summary}
------------------------------------

You can view the answer at {answer_url}.

Regards,
{site_name}
{site_url}', 'sabai-discuss')
                ),
            ),       
            'admin_question_posted' => array(
                'type' => 'admin',
                'title' => __('Question Posted Admin Notification Email', 'sabai-discuss'),
                'description' => __('If enabled, a notification email is sent to administrators whenever a question that requires approval is posted.', 'sabai-discuss'),
                'tags' => $this->_getQuestionTags(),
                'enable' => true,
                'email' => array(
                    'subject' => __('[{site_name}] A new question (ID: {question_id}) that requires approval has been posted', 'sabai-discuss'),
                    'body' => __('Hi {recipient_name},
                
A new question titled "{question_title}" has been posted on {question_date}.

Posted by {question_author_name} ({question_author_email}):

------------------------------------
{question_summary}
------------------------------------

You can view the question at {question_url}.

Regards,
{site_name}
{site_url}', 'sabai-discuss'),
                ),
            ),
            'admin_answer_posted' => array(
                'type' => 'admin',
                'title' => __('Answer Posted Admin Notification Email', 'sabai-discuss'),
                'description' => __('If enabled, a notification email is sent to administrators whenever an answer that requires approval is posted.', 'sabai-discuss'),
                'tags' => array_merge($this->_getAnswerTags(), $this->_getQuestionTags()),
                'enable' => true,
                'email' => array(
                    'subject' => __('[{site_name}] A new answer (ID: {answer_id}) that requires approval has been posted', 'sabai-discuss'),
                    'body' => __('Hi {recipient_name},
                
A new answer to the question "{question_title}" has been posted on {answer_date}.

Posted by {answer_author_name} ({answer_author_email}):

------------------------------------
{answer_summary}
------------------------------------

You can view the answer at {answer_url}.

Regards,
{site_name}
{site_url}', 'sabai-discuss')
                ),
            ),
            'question_flagged' => array(
                'type' => 'roles',
                'title' => __('Question Flagged Notification Email', 'sabai-discuss'),
                'description' => __('If enabled, a notification email is sent to users of selected roles whenever a question is flagged.', 'sabai-discuss'),
                'tags' => array_merge($this->_getFlagTags(), $this->_getQuestionTags()),
                'enable' => false,
                'email' => array(
                    'subject' => __('[{site_name}] Question (ID: {question_id}) has been flagged', 'sabai-discuss'),
                    'body' => __('Hi {recipient_name},
                
The following question has been flagged on {flag_date} by {flag_user_name} ({flag_user_email}):

------------------------------------
{question_title}
------------------------------------

Reason: {flag_reason}
Score: {flag_score} (total: {flag_score_total})

You can view the question at {question_url}.

Regards,
{site_name}
{site_url}', 'sabai-discuss'),
                ),
            ),
            'answer_flagged' => array(
                'type' => 'roles',
                'title' => __('Answer Flagged Notification Email', 'sabai-discuss'),
                'description' => __('If enabled, a notification email is sent to users of selected roles whenever an answer is flagged.', 'sabai-discuss'),
                'tags' => array_merge($this->_getFlagTags(), $this->_getAnswerTags(), $this->_getQuestionTags()),
                'enable' => false,
                'email' => array(
                    'subject' => __('[{site_name}] Answer (ID: {answer_id}) has been flagged', 'sabai-discuss'),
                    'body' => __('Hi {recipient_name},
                
The following answer has been flagged on {flag_date} by {flag_user_name} ({flag_user_email}):

------------------------------------
{answer_summary} (question: {question_title})
------------------------------------

Reason: {flag_reason}
Score: {flag_score} (total: {flag_score_total})

You can view the answer at {answer_url}.

Regards,
{site_name}
{site_url}', 'sabai-discuss')
                ),
            ),
        );
    }
    
    private function _getQuestionTags()
    {
        return array('{question_id}', '{question_title}', '{question_summary}', '{question_author_name}', '{question_author_email}', '{question_url}', '{question_date}');
    }
    
    private function _getAnswerTags()
    {
        return array('{answer_id}', '{answer_summary}', '{answer_author_name}', '{answer_author_email}', '{answer_url}', '{answer_date}');
    }
    
    private function _getContentTags()
    {
        return array('{content_id}', '{content_title}', '{content_author_name}', '{content_author_email}', '{content_url}', '{content_date}');
    }
            
    protected function _getCommentTags()
    {
        return array('{comment_id}', '{comment_author_name}', '{comment_author_email}', '{comment_date}', '{comment_summary}');
    }
    
    protected function _getFlagTags()
    {
        return array('{flag_id}', '{flag_user_name}', '{flag_user_email}', '{flag_date}', '{flag_reason}', '{flag_score}', '{flag_score_total}');
    }
}