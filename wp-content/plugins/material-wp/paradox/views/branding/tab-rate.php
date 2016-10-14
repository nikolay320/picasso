<h3><?php _e('How to rate our plugin', $this->td); ?></h3>
<p><?php _e('We work very hard to bring to life the best solutions to improve your WordPress experience, so, if you like what you see, let us know by rating our plugin on CodeCanyon.', $this->td); ?></p>
<p><?php _e('Your rating also helps us to understand what we need to improve and what do you think should be the next step of our development process.', $this->td); ?></p>
<p><?php printf(__('To rate, go to your <a href="%s">CodeCanyon Dashboard</a>, and click on the Downloads tab.', $this->td), 'http://codecanyon.net/author_dashboard'); ?></p>
<p><?php printf(__('Search for the plugin <strong>%s</strong> and select the number of stars you want to give.', $this->td), $this->getPluginInfo('Name')); ?></p>
<p><img src="<?php echo $this->url('assets/img/rate-our-plugin.png'); ?>" alt="Rate our plugin"></p>
<p><?php _e('Thank you.', $this->td); ?></p>