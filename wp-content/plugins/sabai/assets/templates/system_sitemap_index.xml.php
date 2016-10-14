<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($sitemaps as $sitemap):?>
<?php   if (!empty($sitemap['count']) && $sitemap['count'] > $urls_perpage): $pages = ceil($sitemap['count'] / $urls_perpage);?>
<?php     for ($i = 1; $i <= $pages; ++$i): $sitemap['loc']['params'] = array('p' => $i) + $sitemap['loc']['params'];?>
   <sitemap>
      <loc><?php echo $sitemap['loc'];?></loc>
      <lastmod><?php echo date('c', $sitemap['lastmod']);?></lastmod>
   </sitemap>
<?php     endfor;?>
<?php   else:?>
   <sitemap>
      <loc><?php echo $sitemap['loc'];?></loc>
      <lastmod><?php echo date('c', $sitemap['lastmod']);?></lastmod>
   </sitemap>
<?php   endif;?>
<?php endforeach;?>
</sitemapindex>