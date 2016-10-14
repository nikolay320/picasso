<?php
class Sabai_Addon_Questions_Helper_CreateSampleData extends Sabai_Helper
{
    public function help(Sabai $application, $addonName)
    {
        $questions_addon = $application->getAddon($addonName);
        
        // Create Category/Tag
        try {
            $category = $application->Entity_Save($questions_addon->getCategoriesBundleName(), array(
                'taxonomy_term_title' => 'SabaiDiscuss',
                'taxonomy_body' => __('SabaiDiscuss is a premium questions and answers plugin for WordPress', 'sabai-discuss'),
            ));
        } catch (Sabai_Addon_Taxonomy_TermExistsException $e) {
            $category = $e->getTerm();
        }
        try {
            $tag = $application->Entity_Save($questions_addon->getTagsBundleName(), array(
                'taxonomy_term_title' => 'SabaiDiscuss',
                'taxonomy_body' => __('SabaiDiscuss is a premium questions and answers plugin for WordPress', 'sabai-discuss'),
            ));
        } catch (Sabai_Addon_Taxonomy_TermExistsException $e) {
            $tag = $e->getTerm();
        }
        
        // Add Question
        $question_values = array(
            'content_post_title' => __('Congratulations! You have successfully installed SabaiDiscuss', 'sabai-discuss'),
            'content_body' => __('Thank you for choosing SabaiDiscuss as your preferred discussion tool for your WordPress website. We hope you find it useful in achieving your needs.', 'sabai-discuss'),
            'content_featured' => array('value' => 5, 'featured_at' => time()),
            'questions_resolved' => 1,
            'questions_categories' => array($category->getId() => $category->getSlug()),
            'questions_tags' => array($tag->getId() => $tag->getSlug()),
            
        );
        $question = $application->Entity_Save($questions_addon->getQuestionsBundleName(), $question_values);
        // Add Answer
        $answer_values = array(
            'content_body' => __('Thanks again for choosing SabaiDiscuss as your preferred discussion tool!', 'sabai-discuss'),
            'content_parent' => $question->getId(),
            'questions_answer_accepted' => array('score' => 1, 'accepted_at' => time()),
        );
        $answer = $application->Entity_Save($questions_addon->getAnswersBundleName(), $answer_values);
        
        // Upvote both question and answer, and then mark them as favorites
        $application->Voting_CastVote($question, 'updown', 1);
        $application->Voting_CastVote($answer, 'updown', 1);
        $application->Voting_CastVote($question, 'favorite', 1);
        $application->Voting_CastVote($answer, 'favorite', 1);
        
        // Add Comment to Question
        $c_model = $application->getModel(null, 'Comment');
        $comment1 = $c_model->create('Post')->markNew();
        $comment1->entity_id = $question->getId();
        $comment1->entity_bundle_id = $application->Entity_Bundle($question)->id;
        $comment1->body = $body = __('Hi, this is a comment.', 'sabai-discuss');
        $comment1->body_html = $body;
        $comment1->user_id = $application->getUser()->id;
        $comment1->status = Sabai_Addon_Comment::POST_STATUS_FEATURED;
        $comment1->published_at = time();       
        // Add Comment to Answer
        $comment2 = $c_model->create('Post')->markNew();
        $comment2->entity_id = $answer->getId();
        $comment2->entity_bundle_id = $application->Entity_Bundle($answer)->id;
        $comment2->body = $body = __('Hi, this is a comment.', 'sabai-discuss');
        $comment2->body_html = $body;
        $comment2->user_id = $application->getUser()->id;
        $comment2->status = Sabai_Addon_Comment::POST_STATUS_FEATURED;
        $comment2->published_at = time();
        $c_model->commit();
    }
}
