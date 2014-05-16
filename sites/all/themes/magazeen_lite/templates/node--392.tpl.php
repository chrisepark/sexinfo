<?php include 'utils/content_tag.php' ?>

<?php
  // Decode /data/topics.json as associative array
  function getTopics() {
    $fileData = file_get_contents("data/topics.json", true);
    return json_decode($fileData, true);
  }

  // TODO: remove this
  $GLOBALS['topics'] = getTopics();

  // Render a half-width topic box for a specified topic name
  //
  //  $size - The size of the section, 'half' or 'quarter'
  //  $topicName - Name of the topic JSON section, e.g. 'The Sexual Response Cycle'
  //
  // The heredoc can't be indented!
  // See http://php.net/manual/en/language.types.string.php#language.types.string.syntax.heredoc
  function renderTopicSection($size, $topicName) {
    $topics = $GLOBALS['topics'];
    $topic  = $topics[$topicName];
    $image  = path_to_theme() . '/images/topics/' .  $topic['image'];

    // Pre-construct children list items to substitute into heredoc string
    // Ex: <li class="text-on-image-article"><a href="http://...">Sex Around The World</a></li>
    $children = "";
    foreach($topic['children'] as $name => $url) {
      $link = content_tag("a", $name, array("href" => $url));
      $children .= content_tag('li', $link, array('class' => 'text-on-image-article'));
    }

return <<<HTML
<div class="topic-{$size}">
  <div class="text-on-image" style="background-image: url('$image')">
    <div class="text-on-image-tint">
    <div class="text-on-image-text">{$topicName}</div>
      <ul class="text-on-image-articles">{$children}</ul>
    </div>
  </div>
</div>
HTML;
  }

  function renderTopicHalf($topicName) {
    return renderTopicSection('half', $topicName);
  }

  function renderTopicQuarter($topicName) {
    return renderTopicSection('quarter', $topicName);
  }

  function renderNav() {
      // Query DB for top level topics
      $topics = db_query('SELECT * FROM taxonomy_term_data A, taxonomy_term_hierarchy B WHERE A.tid=B.tid AND A.vid=3 AND B.parent=0 ORDER BY weight ASC');

      // Generate html from db result
      $result = <<<HTML
<ul class="topics-nav">
  <div class="header-container">
    <h4>Explore Our Topics!</h4>
  </div>
HTML;

      // For each topic, make a link in the list
      foreach($topics as $topic) {
        $result = $result . sprintf("<li><a href='#%s'>%s</a></li>", urlencode($topic->name), $topic->name);
      }

      // Return generated HTML
      return $result . "</ul>";
  }

  function renderTopics() {
      // Query DB for top level topics
      $topics = db_query('SELECT * FROM taxonomy_term_data A, taxonomy_term_hierarchy B WHERE A.tid=B.tid AND A.vid=3 AND B.parent=0 ORDER BY weight ASC');

      $result = "<div class=\"topics-container\">";

      // For each topic, make a link in the list
      foreach($topics as $topic) {
        $result = $result . renderTopic($topic);
      }

      // Return generated HTML
      return $result . "</div>";
  }

  function renderTopic($topic) {
    $name = $topic->name;
    $target = urlencode($name);
    $sections = renderSections($topic);
    return <<<HTML
<div class="parent-topic" id="$target">
  <h2>$name</h2>
  $sections
</div><!-- .parent-topic -->
HTML;
  }

  function renderSections($topic) {
    $sections = db_query('SELECT * FROM taxonomy_term_hierarchy A, taxonomy_term_data B WHERE A.tid = B.tid AND parent=' . $topic->tid);
    $sections_html = '';

    foreach ($sections as $section) {
      $sections_html .= renderSection($section);
    }

    return $sections_html;
  }

  function renderSection($section) {
    $num_articles = 0;
    $children_html = '';

    // Generate children html which is links to articles
    $articles = db_query('SELECT DISTINCT * FROM taxonomy_index A, node B WHERE A.nid = B.nid AND A.tid=' . $section->tid);
    foreach($articles as $nid) {
      $num_articles += 1;
      $article = db_query('SELECT * FROM node WHERE nid='.$nid->nid)->fetch();
      $children_html = $children_html . sprintf("<li class='text-on-image-article'><a href=\"node/%d\">%s</a></li>", $article->nid, $article->title);
    }

    $section_name = $section->name;
    $size = $num_articles > 6 ? 'half' : 'quarter';
    $image = 'sites/all/themes/magazeen_lite/images/topics/kinky_sex_paraphilia.jpg';
    return <<<HTML
<div class="topic-{$size}">
  <div class="text-on-image" style="background-image: url('$image')">
    <div class="text-on-image-tint">
    <div class="text-on-image-text">{$section_name}</div>
      <ul class="text-on-image-articles">{$children_html}</ul>
    </div>
  </div>
</div>
HTML;


  }

  echo renderNav();
  echo renderTopics();
  ?>


  <div class="parent-topic" id="basics_of_sexuality">
    <h2>Sex Across the Lifecycle</h2>

      <?php echo renderTopicHalf('Adolescent Sexuality') ?>

      <?php echo renderTopicQuarter('Aging and the Sexual Response') ?>
      <?php echo renderTopicQuarter('Talking About Sex') ?>
  </div><!-- .parent-topic -->

</div><!-- .topics-container -->
