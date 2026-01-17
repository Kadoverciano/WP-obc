<?php
/*
Template Name: Аккордеон
*/

?>

<?php get_header(); ?>



<div class="container">
    <div class="page-container">
        <h1><?php the_title(); ?></h1>
        <div class="page-text"><?php the_content(); ?></div>
        <div class="accordion">
            <?php if ( have_rows( 'accordion' ) ) : ?>
                <?php while ( have_rows( 'accordion' ) ) : the_row(); ?>
                    <div class="accordion-item">
                        <a href="" class="accordion-title"><?php the_sub_field( 'title-acc' ); ?></a>
                        <div class="accordion-content"><?php the_sub_field( 'text-acc' ); ?></div>
                    </div>
                    
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</div>





<?php get_footer();?>