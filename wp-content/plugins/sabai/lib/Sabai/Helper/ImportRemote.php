<?php
class Sabai_Helper_ImportRemote extends Sabai_Helper
{
    /**
     * Imports content using ajax to a specific part of the page
     *
     * @param Sabai $application
     * @param string $id
     * @param mixed $url
     * @param string $callback Javascript callback func
     * @param bool $return
     */
    public function help(Sabai $application, $id, $url, $callback = null, $return = false)
    {
        $url = $application->Url($url); // convert to SabaiFramework_Application_Url
        $url['separator'] = '&';
        $func = $return ? 'sprintf' : 'printf';
        return $func('<script type="text/javascript">
jQuery(document).ready(function() {
  SABAI.load("%s", "%s", %s);
});
</script>', $id, $url, isset($callback) ? $callback : 'null');
    }
}