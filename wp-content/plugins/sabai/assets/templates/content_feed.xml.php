<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom"<?php foreach ($namespaces as $namespace => $namespace_url):?> <?php printf('xmlns:%s="%s"', Sabai::h($namespace), Sabai::h($namespace_url));?><?php endforeach;?>>
  <channel>
    <title><?php Sabai::_h($title);?> - <?php Sabai::_h($SITE_NAME);?></title>
    <description><?php Sabai::_h($description);?></description>
    <link><?php Sabai::_h($link);?></link>
    <lastBuildDate><?php echo date(DATE_RFC822, $build_date);?></lastBuildDate>
    <atom:link href="<?php echo $this->Url($CURRENT_ROUTE);?>.xml" rel="self" type="application/rss+xml" />
<?php foreach ($items as $item):?> 
    <item>
      <title><?php Sabai::_h($item['title']);?></title>
      <description><![CDATA[<?php echo $item['content'];?>]]></description>
      <content:encoded><![CDATA[<?php echo $item['content'];?>]]></content:encoded>
      <link><?php Sabai::_h($item['link']);?></link>
      <guid><?php Sabai::_h($item['guid']);?></guid>
      <dc:creator><?php Sabai::_h($item['author']);?></dc:creator>
      <pubDate><?php echo date(DATE_RFC822, $item['pub_date']);?></pubDate>
<?php   foreach ($item['extras'] as $tag => $value):?>
      <<?php echo $tag;?>><?php Sabai::_h($value);?></<?php echo $tag;?>>
<?php   endforeach;?>
    </item>
<?php endforeach;?>
  </channel>
</rss>
