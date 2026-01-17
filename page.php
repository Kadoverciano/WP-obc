<?php get_header(); ?>

<main class="page">
  <div class="container">
    <?php
    if ( have_posts() ) :
        while ( have_posts() ) : the_post(); ?>
        
            <article id="post-<?php the_ID(); ?>" <?php post_class('page-content'); ?>>
                <h1 class="page-title"><?php the_title(); ?></h1>
                
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="page-thumbnail">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>

                <div class="page-body">
                    <?php the_content(); ?>
                </div>
            </article>
        
        <?php endwhile; ?>
       
    <?php endif; ?>
  </div>
</main>

<?php get_footer(); ?>
