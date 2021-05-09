<?php
/**
 *
 *  @copyright 2008 - https://www.clicshopping.org
 *  @Brand : ClicShopping(Tm) at Inpi all right Reserved
 *  @Licence GPL 2 & MIT
 *  @licence MIT - Portion of osCommerce 2.4
 *  @Info : https://www.clicshopping.org/forum/trademark/
 *
 */

  use ClicShopping\OM\HTML;
  use ClicShopping\OM\Registry;
  use ClicShopping\OM\CLICSHOPPING;

  class he_header_bootstrap_caroussel {
    public string $code;
    public string $group;
    public string $title;
    public string $description;
    public ?int $sort_order = 0;
    public bool $enabled = false;
    public $pages;

    public function __construct() {
      $this->code = get_class($this);
      $this->group = basename(__DIR__);

      $this->title = CLICSHOPPING::getDef('module_header_boostrap_caroussel_title');
      $this->description = CLICSHOPPING::getDef('module_header_boostrap_caroussel_description');

      if (\defined('MODULE_HEADER_BOOSTRAP_CAROUSSEL_STATUS')) {
        $this->sort_order = MODULE_HEADER_BOOSTRAP_CAROUSSEL_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_BOOSTRAP_CAROUSSEL_STATUS == 'True');
        $this->pages = MODULE_HEADER_BOOSTRAP_CAROUSSEL_DISPLAY_PAGES;
      }
    }

    public function execute() {
      $CLICSHOPPING_Customer = Registry::get('Customer');
      $CLICSHOPPING_Db = Registry::get('Db');
      $CLICSHOPPING_Template = Registry::get('Template');
      $CLICSHOPPING_Language = Registry::get('Language');
      $CLICSHOPPING_Category = Registry::get('Category');

      if (empty($CLICSHOPPING_Category->getPath())) {

        $content_width = (int)MODULE_HEADER_BOOSTRAP_CAROUSSEL_CONTENT_WIDTH;

        if ($CLICSHOPPING_Customer->getCustomersGroupID() == 0) {

          $Qbanners = $CLICSHOPPING_Db->prepare('select banners_url,
                                                  banners_image,
                                                  banners_html_text
                                           from :table_banners
                                           where banners_group = :banners_group
                                           and status = 1
                                           and (languages_id = :languages_id or languages_id = 0)
                                           and (customers_group_id = 0 or customers_group_id = 99)
                                           ');
          $Qbanners->bindValue(':banners_group', HTML::outputProtected(MODULE_HEADER_BOOSTRAP_CAROUSSEL_BANNER_GROUP));
          $Qbanners->bindInt(':languages_id', (int)$CLICSHOPPING_Language->getId());

          $Qbanners->execute();

        } else {

          $Qbanners = $CLICSHOPPING_Db->prepare('select banners_url,
                                                  banners_image,
                                                  banners_html_text
                                           from :table_banners
                                           where banners_group = :banners_group
                                           and status = 1
                                           and (languages_id = :languages_id or languages_id = 0)
                                           and (customers_group_id = :customers_group_id or customers_group_id = 99)
                                           ');
          $Qbanners->bindValue(':banners_group', HTML::outputProtected(MODULE_HEADER_BOOSTRAP_CAROUSSEL_BANNER_GROUP));
          $Qbanners->bindInt(':customers_group_id', $CLICSHOPPING_Customer->getCustomersGroupID());
          $Qbanners->bindInt(':languages_id', (int)$CLICSHOPPING_Language->getId());

          $Qbanners->execute();
        }

        if ($Qbanners->rowCount() > 0 ) {

          $footer_tag = '<!-- Footer header caroussel start -->' . "\n";

          $footer_tag .= '<script>';
           $footer_tag .= 'var myCarousel = document.querySelector("#myCarouselHeaderBoostrapCaroussel") ';
           $footer_tag .= 'var carousel = new bootstrap.Carousel(myCarouselHeaderBoostrapCaroussel, { ';
           $footer_tag .= 'interval: ' . ( int )MODULE_HEADER_BOOSTRAP_CAROUSSEL_FADE_TIME . ', ';
           $footer_tag .= 'wrap: false ';
           $footer_tag .= '}) ';

          $footer_tag .= '</script>' . "\n";

          $footer_tag .= '<!-- Footer header caroussel end -->' . "\n";

          $CLICSHOPPING_Template->addBlock($footer_tag, 'footer_scripts');

          $body_text = '<!-- Start Banner responsive  -->' . "\n";
          $body_text .= '<div class="clearfix"></div>';
          $body_text .= '<div>';
          $body_text .= '<div class="' . BOOTSTRAP_CONTAINER . '">';

          $indicators = '<!-- Indicators -->' . "\n";
          $indicators .= ' <div id="myCarouselHeaderBoostrapCaroussel" class="carousel slide" data-bs-ride="carousel">';
          $indicators .= '<ol class="carousel-indicators">';

          $wrapper_slides = '<!-- Wrapper for slides -->' . "\n";
          $wrapper_slides .= '<div class="carousel-inner">';

          $counter = 0;

          while ($banner = $Qbanners->fetch()) {
            $indicators .= '  <li data-bs-target="#myCarouselHeaderBoostrapCaroussel" data-bs-slide-to="' . $counter . '"' . ($counter == 0 ? ' class="active"' : '') . '></li>';

            $wrapper_slides .= '<div class="carousel-item itemHeaderBoostrapCaroussel' . ($counter == 0 ? ' active' : '') . '">';

            if ($banner['banners_url'] != '') {
              $wrapper_slides .= '<a href="' . CLICSHOPPING::link('redirect.php', 'action=banner&goto=' . $Qbanners->valueInt('banners_id')) . '">';
            }


            if (!empty($banner['banners_image'])) {
              $wrapper_slides .= HTML::image($CLICSHOPPING_Template->getDirectoryTemplateImages() . $Qbanners->value('banners_image'), HTML::outputProtected(STORE_NAME), null, null, null, true);
              $wrapper_slides .= '<div class="carousel-caption">&nbsp;</div>';

            } else {
              $wrapper_slides .= '<div class="carousel-caption">' . $Qbanners->value('banners_html_text') . '</div>';
            }

            if (!empty($Qbanners->value('banners_url'))) {
              $wrapper_slides .= '</a>';
            }

            $wrapper_slides .= ' </div>' . "\n";
            $counter++;
          }


          $indicators .= '</ol>';  // close indicator
          $wrapper_slides .= '</div>';  // wrapper close

          $body_text .= '</div>';
          $body_text .= '</div>';
          $body_text .= '</div>';
          $body_text .= '<div class="clearfix"></div>' . "\n";

          $CLICSHOPPING_Template->addBlock($body_text, $this->group);

          $header_template = '<!-- bootstrap overlay start -->';

          ob_start();
          require_once($CLICSHOPPING_Template->getTemplateModules($this->group . '/content/header_boostrap_caroussel'));
          $header_template .= ob_get_clean();

          $header_template .= '<!-- header boostrap caroussel end -->' . "\n";

          $CLICSHOPPING_Template->addBlock($header_template, $this->group);

        }
      }
    }

    public function isEnabled() {
      return $this->enabled;
    }

    public function check() {
      return \defined('MODULE_HEADER_BOOSTRAP_CAROUSSEL_STATUS');
    }

    public function install() {
      $CLICSHOPPING_Db = Registry::get('Db');

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Enable Banner Rotator ?',
          'configuration_key' => 'MODULE_HEADER_BOOSTRAP_CAROUSSEL_STATUS',
          'configuration_value' => 'True',
          'configuration_description' => 'Do you want to show the banner rotator ?',
          'configuration_group_id' => '6',
          'sort_order' => '8',
          'set_function' => 'clic_cfg_set_boolean_value(array(\'True\', \'False\'))',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Select the width of the image?',
          'configuration_key' => 'MODULE_HEADER_BOOSTRAP_CAROUSSEL_CONTENT_WIDTH',
          'configuration_value' => '12',
          'configuration_description' => 'Select a number between 1 and 12',
          'configuration_group_id' => '6',
          'sort_order' => '1',
          'set_function' => 'clic_cfg_set_content_module_width_pull_down',
          'date_added' => 'now()'
        ]
      );


      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Fade Time',
          'configuration_key' => 'MODULE_HEADER_BOOSTRAP_CAROUSSEL_FADE_TIME',
          'configuration_value' => '4000',
          'configuration_description' => 'The time it takes to fade from one banner to the next. 1000 = 1 second',
          'configuration_group_id' => '6',
          'sort_order' => '2',
          'set_function' => '',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Hold Time',
          'configuration_key' => 'MODULE_HEADER_BOOSTRAP_CAROUSSEL_HOLD_TIME',
          'configuration_value' => '4000',
          'configuration_description' => 'The time each banner is shown. 1000 = 1 second',
          'configuration_group_id' => '6',
          'sort_order' => '3',
          'set_function' => '',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Please, choose the banner group to display the banner',
          'configuration_key' => 'MODULE_HEADER_BOOSTRAP_CAROUSSEL_BANNER_GROUP',
          'configuration_value' => SITE_THEMA.'_banner_boostrap_header',
          'configuration_description' => 'Please, choose your banner group<br /><br /><strong>Note :</strong><br /><i>The group to display the banner must be include when your create the banner Marketing / banner management</i><br /><br /><p style="color:#FF0000;"><strong>This module does not work start date, end date, printing functions.</strong>.</p>',
          'configuration_group_id' => '6',
          'sort_order' => '4',
          'set_function' => '',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Veuillez un indiquer un nom maximal de produits en arrivage à afficher',
          'configuration_key' => 'MODULE_HEADER_BOOSTRAP_CAROUSSEL_SORT_ORDER',
          'configuration_value' => '5',
          'configuration_description' => 'Sort order of display. Lowest is displayed first. The sort order must be different on every module',
          'configuration_group_id' => '6',
          'sort_order' => '6',
          'set_function' => '',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Sort Order',
          'configuration_key' => 'MODULE_HEADER_BOOSTRAP_CAROUSSEL_DISPLAY_PAGES',
          'configuration_value' => 'all',
          'configuration_description' => 'Select the page where the caroussel will be displayed',
          'configuration_group_id' => '6',
          'sort_order' => '7',
          'set_function' => 'clic_cfg_set_select_pages_list',
          'date_added' => 'now()'
        ]
      );
    }

    public function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }

    public function keys() {
      return array(
                   'MODULE_HEADER_BOOSTRAP_CAROUSSEL_STATUS',
                   'MODULE_HEADER_BOOSTRAP_CAROUSSEL_CONTENT_WIDTH',
                   'MODULE_HEADER_BOOSTRAP_CAROUSSEL_BANNER_GROUP',
                   'MODULE_HEADER_BOOSTRAP_CAROUSSEL_FADE_TIME',
                   'MODULE_HEADER_BOOSTRAP_CAROUSSEL_HOLD_TIME',
                   'MODULE_HEADER_BOOSTRAP_CAROUSSEL_SORT_ORDER',
                   'MODULE_HEADER_BOOSTRAP_CAROUSSEL_DISPLAY_PAGES'
                  );
    }
  }
