<?php
class Sabai_Addon_Voting_Helper_FlagOptions extends Sabai_Helper
{
    public function help(Sabai $application)
    {
        return array(
            Sabai_Addon_Voting::FLAG_VALUE_SPAM => __('It is spam', 'sabai'),
            Sabai_Addon_Voting::FLAG_VALUE_OFFENSIVE => __('It contains offensive language or content', 'sabai'),
            Sabai_Addon_Voting::FLAG_VALUE_OFFTOPIC => __('It does not belong here', 'sabai'),
            Sabai_Addon_Voting::FLAG_VALUE_OTHER => __('Other reason (Enter comment below)', 'sabai'),
        );
    }
}