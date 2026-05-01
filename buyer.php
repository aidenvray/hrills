<?php
/***************************************************************************
*
*	ProjectTheme - copyright (c) - sitemile.com
*	The only project theme for wordpress on the world wide web.
*
*	Coder: Andrei Dragos Saioc
*	Email: sitemile[at]sitemile.com | andreisaioc[at]gmail.com
*	More info about the theme here: http://sitemile.com/products/wordpress-project-freelancer-theme/
*	since v1.2.5.3
*
***************************************************************************/
function pt_freelancer_area_timing_status_customer($row)
{
  $now = current_time( 'timestamp' );
  if($row->completion_date < $now)
  {
      echo '<div class="alert alert-danger alert-smaller-padding"><small class="">'.sprintf(__('Deadline has been delayed.','ProjectTheme')) . '</small></div>';
  }

}
//*******************************************************
//
//      freelancer area
//
//*******************************************************

function pt_show_buyer_payment_status($row)
{
  $date_format = get_option('date_format');


  $ProjectTheme_payment_model = get_option('ProjectTheme_payment_model');
  if($ProjectTheme_payment_model == "ewallet_only")
  {

    $order = new project_orders($row->id);

    if($order->has_escrow_deposited() == false)
    {
        $lnk = ProjectTheme_get_payments_page_url2('escrow', $row->id);
        echo '<div class="alert alert-warning alert-smaller-padding"><small class="">'.sprintf(__('The escrow has not been deposited yet. <a href="%s">Click here</a> to deposit escrow.','ProjectTheme'), $lnk) . '</small></div>';
    }
    else {
      $obj = $order->get_escrow_object();

      echo '<div class="alert alert-success alert-smaller-padding"><small class="">'.sprintf(__('Escrow was deposited on %s.','ProjectTheme'), date_i18n($date_format, $obj->datemade)) . '</small></div>';

    }

  }
  elseif($ProjectTheme_payment_model == "invoice_model_pay_outside")
  {

    // right now nothing here, maybe in the future we can put a message
    // but bills will appear in their finances area
    // payments are done outside the website

      echo '<div class="alert alert-warning alert-smaller-padding"><small class="">
      '.sprintf(__('The payment for this project is done outside of the website.','ProjectTheme')) . '</small></div>';

  }
  else {
    // code...
    //

    $order = new project_orders($row->id);

    if($order->has_marketplace_payment_been_deposited() == false)
    {
        $lnk = ProjectTheme_get_payments_page_url2('paysplit', $row->id);
        echo '<div class="alert alert-warning alert-smaller-padding"><small class="">'.sprintf(__('This project has not been paid. <a href="%s">Click here</a> to make payment.','ProjectTheme'), $lnk) . '</small></div>';
    }
    else {
      $obj = $order->get_marketplace_payment_object();

      echo '<div class="alert alert-success alert-smaller-padding"><small class="">'.sprintf(__('Payment was sent on %s.','ProjectTheme'), date_i18n($date_format, $obj->datemade)) . '</small></div>';

    }
  }

  do_action('pt_on_buyer_payment_status', $row);
}


//*******************************************************
//
//      freelancer area
//
//*******************************************************

function project_theme_my_account_buyer_area_fnc()
{
       ob_start();

				global $current_user, $wp_query, $wpdb;
				$current_user=wp_get_current_user();

				$uid = $current_user->ID;



        $date_format =  get_option( 'date_format' );

				get_template_part ( 'lib/my_account/aside-menu'  );


				?>


				<div class="page-wrapper" style="display:block">
					<div class="container"  >


					<?php



					do_action('pt_for_demo_work_3_0');


?>



<div class="row">
<div class="col-sm-12 col-lg-8">




<div class="page-header">
              <h1 class="page-title">
                <?php echo sprintf(__('My Requests','ProjectTheme')  ) ?>
              </h1>
            </div></div></div>

<div class="trade-inline" id="rqTipsBar">

  <span class="ts-icon">
    <svg viewBox="0 0 24 24" fill="none">
      <path d="M12 2L4 5V11C4 16.5 7.8 21.4 12 22C16.2 21.4 20 16.5 20 11V5L12 2Z"/>
    </svg>
  </span>

  <span>
    <strong>Manage requests</strong> · Track quotes · Communicate with suppliers
  </span>

  <a href="#" id="rqViewTips">View tips</a>
</div>

<div id="rqTipsBox" class="ts-tips-box" style="display:none;">
  <ul class="ts-tips-list">

    <!-- MANAGE -->
    <li>
      <span class="ts-check">
        <svg viewBox="0 0 24 24" fill="none">
          <path d="M4 7h16M4 12h10M4 17h7"/>
        </svg>
      </span>
      <span>
        <strong>Edit, manage or close requests</strong> anytime using the menu
      </span>
    </li>

    <!-- CHAT -->
    <li>
      <span class="ts-check">
        <svg viewBox="0 0 24 24" fill="none">
          <path d="M4 5h16v10H6l-2 2V5z"/>
          <path d="M8 9h8"/>
        </svg>
      </span>
      <span>
        <strong>Start direct chat</strong> with suppliers to clarify details
      </span>
    </li>

    <!-- TRACK -->
    <li>
      <span class="ts-check">
        <svg viewBox="0 0 24 24" fill="none">
          <path d="M12 8v4l3 2"/>
          <path d="M12 22C6.5 22 2 17.5 2 12S6.5 2 12 2s10 4.5 10 10-4.5 10-10 10z"/>
        </svg>
      </span>
      <span>
        <strong>Track new quotes and replies</strong> — stay updated in real time
      </span>
    </li>

  </ul>
</div>
                      
<script>
jQuery(function($){

  const box = $('#rqTipsBox');

  $('#rqViewTips').on('click', function(e){
    e.preventDefault();
    box.fadeToggle(120);
  });

});
</script>



            <?php


            do_action('pt_buyer_area_at_top');



                  $pgid = get_option('ProjectTheme_my_account_buyer_area');
                  if(empty($_GET['pg'])) $pg = 'home';
                  else $pg = $_GET['pg'];

                  //------- active quotes number -----


                  $active_quotes = pt_all_received_bids_number($uid);
                  if($active_quotes > 0)
                  {
                        $active_quotes = '<span class="noti-noti">'.$active_quotes.'</span>';
                  } else $active_quotes = '';

                  //---------pending projects ----------

                  $orders = new project_orders();
                  $pending_proj = $orders->get_number_of_open_orders_for_buyer($uid);
                  if($pending_proj > 0)
                  {
                        $pending_proj = '<span class="noti-noti2">'.$pending_proj.'</span>';
                  } else $pending_proj = '';

                  //--- delivered -------------------

                  $delivered_nr = $orders->get_number_of_delivered_orders_for_buyer($uid);
                  if($delivered_nr > 0)
                  {
                        $delivered_nr = '<span class="noti-noti">'.$delivered_nr.'</span>';
                  } else $delivered_nr = '';


                  //------- unpublished -----


                  $unpub = projecttheme_get_number_of_unpublished($uid);

                  if($unpub > 0)
                  {
                        $unpub = '<span class="noti-noti2">'.$unpub.'</span>';
                  } else $unpub = '';


            ?>



<div class="row">



    	<div class="account-main-area col-xs-12 col-sm-8 col-md-12 col-lg-12">


        <ul class="nav nav-tabs d-none" id="myTab-main" role="tablist">

          <li class="nav-item">
            <a class="nav-link <?php echo $pg == 'home' ? 'active' : '' ?>" id="home-tab"  href="<?php echo ProjectTheme_get_project_link_with_page($pgid, 'home'); ?>" ><?php _e('Active Projects','ProjectTheme') ?></a>
          </li>

          <li class="nav-item">
            <a class="nav-link <?php echo $pg == 'quotes' ? 'active' : '' ?>" id="home-tab"  href="<?php echo ProjectTheme_get_project_link_with_page($pgid, 'quotes'); ?>" ><?php printf(__('Active Quotes %s','ProjectTheme'), $active_quotes) ?></a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo $pg == 'pending' ? 'active' : '' ?>" id="profile-tab" href="<?php echo ProjectTheme_get_project_link_with_page($pgid, 'pending'); ?>"><?php printf(__('Pending Projects %s','ProjectTheme'), $pending_proj) ?></a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo $pg == 'delivered' ? 'active' : '' ?>" id="contact-tab" href="<?php echo ProjectTheme_get_project_link_with_page($pgid, 'delivered'); ?>"><?php printf(__('Delivered %s','ProjectTheme'), $delivered_nr) ?></a>
          </li>

          <li class="nav-item">
            <a class="nav-link <?php echo $pg == 'completed' ? 'active' : '' ?>" id="contact-tab" href="<?php echo ProjectTheme_get_project_link_with_page($pgid, 'completed'); ?>"><?php _e('Completed','ProjectTheme') ?></a>
          </li>


          <li class="nav-item">
            <a class="nav-link <?php echo $pg == 'cancelled' ? 'active' : '' ?>" id="contact-tab" href="<?php echo ProjectTheme_get_project_link_with_page($pgid, 'cancelled'); ?>"><?php _e('Cancelled','ProjectTheme') ?></a>
          </li>


          <li class="nav-item">
            <a class="nav-link <?php echo $pg == 'unpublished' ? 'active' : '' ?>" id="contact-tab" href="<?php echo ProjectTheme_get_project_link_with_page($pgid, 'unpublished'); ?>"><?php printf(__('Unpublished %s','ProjectTheme'), $unpub) ?></a>
          </li>

        </ul>


        <?php

          $current_page = empty($_GET['pj']) ? 1 : $_GET['pj'];

          $amount_per_page = 10;
          $offset = ($current_page -1)*$amount_per_page;

          //------------------------------------------------

if($pg == "home")
{
    $prf = $wpdb->prefix;

    $status = $_GET['status'] ?? array();
    if (!is_array($status)) {
        $status = array($status);
    }
    $status = array_map('sanitize_text_field', $status);
    $status = array_values(array_intersect($status, array('open','closed','awarded','unread')));

    $where_meta = '';
    $conditions = array();

    foreach ($status as $s) {
        if ($s === 'open') {
            $conditions[] = "(
                (closed_meta.meta_value IS NULL OR closed_meta.meta_value = '0')
                AND (winner_meta.meta_value IS NULL OR winner_meta.meta_value = '' OR winner_meta.meta_value = '0')
            )";
        } elseif ($s === 'closed') {
            $conditions[] = "(
                closed_meta.meta_value = '1'
                AND (winner_meta.meta_value IS NULL OR winner_meta.meta_value = '' OR winner_meta.meta_value = '0')
            )";
        } elseif ($s === 'awarded') {
            $conditions[] = "(
                winner_meta.meta_value IS NOT NULL
                AND winner_meta.meta_value <> ''
                AND winner_meta.meta_value <> '0'
            )";
        } elseif ($s === 'unread') {
            $conditions[] = "EXISTS (
                SELECT 1
                FROM {$prf}project_bids qb
                INNER JOIN {$prf}rfq_questions qq ON qq.bid_id = qb.id
                WHERE qb.pid = posts.ID
                  AND qq.sender_id <> {$uid}
                  AND qq.is_read_buyer = 0
            )";
        }
    }

    if (!empty($conditions)) {
        $where_meta = " AND (" . implode(" OR ", $conditions) . ")";
    }

    $offset = (int)$offset;
    $amount_per_page = (int)$amount_per_page;
    $uid = (int)$uid;

    $s = "
    SELECT SQL_CALC_FOUND_ROWS posts.*
    FROM {$prf}posts posts
    LEFT JOIN {$prf}postmeta closed_meta
        ON posts.ID = closed_meta.post_id AND closed_meta.meta_key = 'closed'
    LEFT JOIN {$prf}postmeta winner_meta
        ON posts.ID = winner_meta.post_id AND winner_meta.meta_key = 'winner'
    WHERE posts.post_type = 'project'
      AND posts.post_status = 'publish'
      AND posts.post_author = %d
      {$where_meta}
    GROUP BY posts.ID
    ORDER BY
      CASE
        WHEN closed_meta.meta_value IS NULL THEN 0
        WHEN closed_meta.meta_value = '0' THEN 0
        ELSE 1
      END,
      posts.ID DESC
    LIMIT %d, %d
    ";

    $s = $wpdb->prepare($s, $uid, $offset, $amount_per_page);
    $r = $wpdb->get_results($s);
    if (!is_array($r)) { $r = array(); }

    $total_rows = (int) projecttheme_get_last_found_rows();

    $status_qs = '';
    if (!empty($status)) {
        $status_qs = '&status[]=' . implode('&status[]=', array_map('rawurlencode', $status));
    }

    $own_pagination = new own_pagination(
        $amount_per_page,
        $total_rows,
        ProjectTheme_get_project_link_with_page($pgid, 'home' . $status_qs) . "&"
    );

    if(isset($_GET['export']) && $_GET['export'] === 'xls'){
        $filename = 'my-requests-' . date('Ymd-His') . '.xls';
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        nocache_headers();
        header('Content-Disposition: attachment; filename=' . $filename);

        $headers = array('Request ID','Manufacturer','Machine Model','Budget','Offers','Status','Unread Supplier Messages','Created');
        $rows = array();

        foreach($r as $exp_row){
            $exp_pid = (int)$exp_row->ID;
            $exp_manufacturer = (string)get_post_meta($exp_pid, 'manufacturer', true);
            $exp_machine = (string)get_post_meta($exp_pid, 'machine_model', true);
            $exp_budget = get_post_meta($exp_pid, 'ddp_basics', true);
            $exp_budget = ($exp_budget !== null && $exp_budget !== '') ? number_format((int)$exp_budget, 0, '', ' ') . ' $' : '—';
            $exp_winner = get_post_meta($exp_pid, 'winner', true);
            $exp_closed = get_post_meta($exp_pid, 'closed', true);
            $exp_bid_count = (int)projectTheme_number_of_bid($exp_pid);
            $exp_tag = ( ($exp_closed == 1 && empty($exp_winner)) ? 'Closed' : ((empty($exp_winner) && empty($exp_closed)) ? 'Open' : ((!empty($exp_winner) && $exp_closed == 1) ? 'Awarded' : 'Open')) );

            $exp_unread_supplier = (int)$wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*)
                 FROM {$prf}project_bids qb
                 INNER JOIN {$prf}rfq_questions qq ON qq.bid_id = qb.id
                 WHERE qb.pid = %d
                   AND qq.sender_id <> %d
                   AND qq.is_read_buyer = 0",
                $exp_pid,
                $uid
            ));

            $rows[] = [
                'MB'.$exp_pid,
                $exp_manufacturer,
                $exp_machine,
                $exp_budget,
                $exp_bid_count,
                $exp_tag,
                $exp_unread_supplier,
                human_time_diff(get_the_time('U', $exp_pid), current_time('timestamp')) . ' ago'
            ];
        }

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        echo "<html><head><meta charset='UTF-8'><style>
            table{border-collapse:collapse;font-family:Arial,sans-serif;font-size:12px;}
            th,td{border:1px solid #d9d9d9;padding:6px 8px;vertical-align:top;}
            th{background:#f2f2f2;font-weight:700;}
        </style></head><body><table><thead><tr>";
        foreach($headers as $h){ echo '<th>' . esc_html($h) . '</th>'; }
        echo "</tr></thead><tbody>";
        foreach($rows as $rw){
            echo '<tr>';
            foreach($rw as $cell){ echo '<td>' . esc_html((string)$cell) . '</td>'; }
            echo '</tr>';
        }
        echo "</tbody></table></body></html>";

        exit;
    }

    $winner_select_workspace = $_GET['winner_select_workspace'] ?? '';
    if(!empty($winner_select_workspace)){
        ?>
        <div class="mt-4 alert alert-success d-none" bis_skin_checked="1">
            Select Winner Successfully.
            <a href="/?p_action=workspaces&pid=<?php echo (int)$winner_select_workspace; ?>">Workspace</a>
        </div>
        <?php
    }
    ?>

    <form method="GET" class="status-filter">
      <input type="hidden" name="pg" value="home">
      
      <label class="rqq-tag">
        <input type="checkbox" name="status[]" value="open"
          <?php echo in_array('open', $status, true) ? 'checked' : '' ?>>
        Open
      </label>

      <label class="rqq-tag">
        <input type="checkbox" name="status[]" value="closed"
          <?php echo in_array('closed', $status, true) ? 'checked' : '' ?>>
        Closed
      </label>

      <label class="rqq-tag">
        <input type="checkbox" name="status[]" value="awarded"
          <?php echo in_array('awarded', $status, true) ? 'checked' : '' ?>>
        Awarded
      </label>
      
      <label class="rqq-tag">
        <input type="checkbox" name="status[]" value="unread"
          <?php echo in_array('unread', $status, true) ? 'checked' : '' ?>>
        Unread
      </label>

      </form>
          
<?php
$filtered_rows = [];

foreach($r as $row){

    $pid = $row->ID;

    $winner = get_post_meta($pid, 'winner', true);
    $closed = get_post_meta($pid, 'closed', true);

    if ($closed == 1 && empty($winner)) {
        $tag = 'closed';
    } elseif (empty($winner)) {
        $tag = 'open';
    } elseif (!empty($winner) && $closed == 1) {
        $tag = 'awarded';
    } else {
        $tag = 'open';
    }

    $unread_messages_count = (int)$wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*)
             FROM {$prf}project_bids qb
             INNER JOIN {$prf}rfq_questions qq ON qq.bid_id = qb.id
             WHERE qb.pid = %d
               AND qq.sender_id <> %d
               AND qq.is_read_buyer = 0",
            (int)$pid,
            (int)$uid
        )
    );

    if(in_array('unread', $status, true) && $unread_messages_count === 0){
        continue;
    }

    if(!empty($status)){
        $status_filter = array_diff($status, ['unread']);
        if(!empty($status_filter) && !in_array($tag, $status_filter, true)){
            continue;
        }
    }

    $filtered_rows[] = $row;
}
?>

<div class="card" style="border-radius:10px;">
		     
                   <div class="p-3 table-responsive">
					  <table class="rqq-table">
						  <thead>
<tr>
  <th>Request</th>
  <th>Details</th>
  <th>Budget / Offers</th>
  <th class="rqq-th-status">

  <div class="rqq-th-flex">
    
    <a href="?<?php 
        $params = $_GET;
        $params['export'] = 'xls';
        echo esc_attr(http_build_query($params));
    ?>" 
    class="export-icon-xls" 
    title="Export to Excel">

      <svg viewBox="0 0 24 24">
        <path d="M5 3h9l5 5v13H5V3z" fill="none" stroke="currentColor" stroke-width="1.6"/>
        <path d="M14 3v5h5" fill="none" stroke="currentColor" stroke-width="1.6"/>
        <path d="M8 12h8M8 15h8M8 18h5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
      </svg>

    </a>
  </div>

</th>
</tr>
</thead>
						  <tbody>
							 <?php 
				 

                               foreach($filtered_rows as $row)
                               {
								   
								   
								   $pid= $row->ID;
								 
$description     = get_post_field('post_content', $pid); // post content
$finalised_posted = get_post_meta($pid, 'finalised_posted', true);
$country         = get_post_meta($pid, 'country', true);
$city            = get_post_meta($pid, 'city', true);
$type            = get_post_meta($pid, 'type', true);
$condition       = get_post_meta($pid, 'condition', true);
$parts           = get_post_meta($pid, 'parts', true);
$serial_number   = get_post_meta($pid, 'serial_number', true);
$machine_model   = get_post_meta($pid, 'machine_model', true);
$selected_manufacturer    = get_post_meta($pid, 'manufacturer', true);
$made_me_date    = get_post_meta($pid, 'made_me_date', true);	 
$ddp_basics    = get_post_meta($pid, 'ddp_basics', true);		   
$post_author = $row->post_author;
$winner = 	get_post_meta($pid, 'winner', true);
$closed = 	get_post_meta($pid, 'closed', true);
$quote_read = 	get_post_meta($pid, 'quote_read', true);
								   
// echo 	$closed.'--';							   
// echo 	$winner.'<br>';							   
$bid_count = projectTheme_number_of_bid($row->ID);

/* ===============================
   1) NEW QUOTES unread
   old working logic: quote_read post_meta
================================ */
$quote_read = get_post_meta($pid, 'quote_read', true);
$new_quotes_count = 0;

if (is_array($quote_read)) {
    foreach ($quote_read as $value) {
        if ((int)$value === 1) {
            $new_quotes_count++;
        }
    }
}

/* ===============================
   2) NEW MESSAGES unread
   new chat logic: rfq_questions
================================ */
$unread_messages_count = (int)$wpdb->get_var(
    $wpdb->prepare(
        "SELECT COUNT(*)
         FROM {$prf}project_bids qb
         INNER JOIN {$prf}rfq_questions qq ON qq.bid_id = qb.id
         WHERE qb.pid = %d
           AND qq.sender_id <> %d
           AND qq.is_read_buyer = 0",
        (int)$pid,
        (int)$uid
    )
);

/* ===============================
   Status + Offers display
================================ */
$tag = '';
$total_bid = '<span class="quote-count">'.$bid_count.'</span>';

if ($closed == 1 && empty($winner)) {
    $tag = 'Closed';
} elseif (empty($winner) && empty($closed)) {
    $tag = 'Open';

    if ($new_quotes_count > 0) {
        $total_bid = '<span class="quote-badge no-bids">+'.$new_quotes_count.'</span>';
    } else {
        $total_bid = '<span class="quote-count">'.$bid_count.'</span>';
    }
} elseif (!empty($winner) && $closed == 1) {
    $tag = 'Awarded';
} else {
    $tag = 'Open';
}

/* Badge near View for unread messages */
$message_badge_html = '';
if ($unread_messages_count > 0) {
    $message_badge_html = '<span class="rqq-message-badge">+'.$unread_messages_count.'</span>';
}
// 							<td style="vertical-align: top;width: 14.2%;display:flex;gap:5px;">'.$total_bid.' <span class="quote-badge '.($tag == 'Open' ? 'no-bids' : '' ).'">'.(projectTheme_number_of_bid($row->ID) ? '+' : '').projectTheme_number_of_bid($row->ID).'</span> quotes</td>	   

                     echo '
<tr class="quote-head-row" data-bid-count="'.$bid_count.'">

<td style="vertical-align: top;width: 14.2%;">

  <div class="rfq-main-block">

    <!-- 1 строка -->
    <div class="rfq-brand">
      '.$selected_manufacturer.'
    </div>

    <!-- 2 строка -->
    <div class="rfq-id">
      <a href="'.get_permalink($row->ID).'">
        #MB'.$row->ID.'
      </a>
    </div>

    <!-- 3 строка -->
    <div class="rfq-date">
      Created '.human_time_diff(get_the_time('U', $row->ID), current_time('timestamp')).' ago
    </div>

  </div>

</td>      

<td style="width: 14.2%;">
    '.$machine_model.'

    '.(
        !empty($parts) 
        ? '<div class="rqq-part"><b>'.esc_html($parts[0]['name']).'</b></div>'
        : ''
    ).'

    '.(
        count($parts) > 1
        ? '<div class="rqq-part-more">+ '.(count($parts) - 1).' part'.((count($parts) - 1) > 1 ? 's' : '').'</div>'
        : ''
    ).'
</td>      

<td class="rqq-budget-offers">

  <div class="rqq-budget-wrap">

    <div class="rqq-budget">
      '.(($ddp_basics !== null && $ddp_basics !== '') 
          ? number_format((int)$ddp_basics, 0, '', ' ') . ' $' 
          : '—').'
    </div>

    <div class="rqq-offers">
      <span class="quote-count">'.$total_bid.'</span>
      <span class="rqq-offers-label">Offers</span>
    </div>

  </div>

</td>

<td class="rqq-col-status">  
    <div class="rqq-status-block">      

        <div class="rqq-status-top">
            <span class="rqq-tag">'.$tag.'</span>
        </div>

        <div class="rqq-actions-row">

            <span data-project_id="'.$row->ID.'" class="view-quotes">
                <svg class="vq-icon" width="12" height="12" viewBox="0 0 12 12">
                    <path d="M3 4 L6 8 L9 4"
                          stroke="#f59e0b"
                          stroke-width="1.8"
                          fill="none"
                          stroke-linecap="round"
                          stroke-linejoin="round"/>
                </svg>

                <span class="vq-text">View</span>
                '.$message_badge_html.'
            </span>

            '.($tag == 'Open' ? '
            <div class="rqq-actions-menu">
                <span class="rqq-dots">⋯</span>

                <div class="rqq-dropdown">
                    <a href="'.esc_url(home_url('/post-new-2/?edit=1&projectid='.$pid)).'" class="rqq-dropdown-item">
                        Edit RFQ
                    </a>

                    <a href="?close_rfq='.intval($pid).'" 
                       class="rqq-dropdown-item rqq-danger">
                        Close without award
                    </a>
                </div>
            </div>
            ' : '').'

        </div>

    </div>
</td>

</tr>';
								   global $wpdb;
$table_name = $wpdb->prefix . 'project_bids';

$bids = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM `$table_name` WHERE pid = %d",
        $row->ID
    )
);

/* ===== QUOTES ROW ===== */
echo '<tr class="rqq-quote-row" style="display:none;">';
echo '<td colspan="4">';
echo '<table><tbody>';

/* ===== IF BIDS ===== */
if (!empty($bids)) {

    foreach ($bids as $bid) {

        echo '<tr>'; // 🔴 ОБЯЗАТЕЛЬНО

        $args = array(
            'order'          => 'ASC',
            'post_type'      => 'attachment',
            'post_parent'    => $pid,
            'post_author'    => $bid->uid,
            'meta_key'       => 'is_bidding_file',
            'meta_value'     => '1',
            'numberposts'    => -1,
            'post_status'    => null,
        );

        $attachments = get_posts($args);

        $bid_user  = $bid->uid;
        $bid_user_obj = get_user_by('id', $bid_user);
		$bidder_email = ($bid_user_obj && !empty($bid_user_obj->user_email)) ? $bid_user_obj->user_email : '';

        $company_name = get_user_meta($bid_user, 'company_name', true);
        $country = get_user_meta($bid_user, 'country', true);

        /* ===== COMPANY ===== */
        echo '<td>
            <div style="font-weight:600;">'.(!empty($company_name) ? $company_name : 'No company').'</div>
            <div style="font-size:12px;color:#777;">'.$country.'</div>
        </td>';

/* ===== DELIVERY + FILE (FINAL) ===== */
echo '<td class="rqq-delivery-file">

    <div class="rqq-delivery-days">
        <b>'.(int)$bid->days_done.' days</b>
    </div>';

/* ===== FILE ===== */
if (!empty($attachments)) {

    $attachment = $attachments[0];
    $url  = wp_get_attachment_url($attachment->ID);
    $mime = get_post_mime_type($attachment->ID);

    if ($mime === 'application/pdf') {

        echo '<div class="rqq-file-link">
            <a href="'.esc_url($url).'" target="_blank" class="rqq-pdf-clean">

                <svg class="rqq-icon-pdf" width="14" height="14" viewBox="0 0 24 24" fill="none">
                    <path d="M6 2h9l5 5v15a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"
                          stroke="#ef4444" stroke-width="1.5"/>
                    <path d="M14 2v6h6" stroke="#ef4444" stroke-width="1.5"/>
                </svg>

                <span>View PDF</span>
            </a>
        </div>';

    } else {

        echo '<div class="rqq-file-link rqq-no-file">

            <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                <path d="M4 4l16 16" stroke="#9ca3af" stroke-width="1.5"/>
                <path d="M20 4L4 20" stroke="#9ca3af" stroke-width="1.5"/>
            </svg>

            <span>No file</span>

        </div>';
    }

} else {

    echo '<div class="rqq-file-link rqq-no-file">

        <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
            <path d="M4 4l16 16" stroke="#9ca3af" stroke-width="1.5"/>
            <path d="M20 4L4 20" stroke="#9ca3af" stroke-width="1.5"/>
        </svg>

        <span>No file</span>

    </div>';
}

echo '</td>';

        /* ===== PRICE ===== */
echo '<td>
  <div style="font-weight:600;">' . 
    (($bid->bid !== null && $bid->bid !== '') 
      ? number_format((int)$bid->bid, 0, '', ' ') . ' $' 
      : '—') . 
  '</div>
  
</td>';

/* ===== QUESTIONS ===== */
        $questions_table = $wpdb->prefix . 'rfq_questions';
        $buyer_id = (int) get_post_field('post_author', $pid);
        $supplier_id = (int) $bid_user;
        $bid_id = (int) $bid->id;

        $questions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, sender_id, message, created_at FROM {$questions_table} WHERE bid_id = %d ORDER BY id ASC",
                $bid_id
            )
        );
        if(!is_array($questions)) $questions = array();

        $first_sender_id = !empty($questions) ? (int)$questions[0]->sender_id : 0;
        $is_open_lot = ($tag === 'Open');
        $can_buyer_write = ((int)$uid === $buyer_id && $is_open_lot);
        $can_supplier_write = ((int)$uid === $supplier_id && $is_open_lot && $first_sender_id === $buyer_id);
        $can_write_question = ($can_buyer_write || $can_supplier_write);
      
/* ===== UNREAD PER BID (для Chat кнопки) ===== */
$has_unread_for_this_bid = (int)$wpdb->get_var(
    $wpdb->prepare(
        "SELECT COUNT(*)
         FROM {$questions_table}
         WHERE bid_id = %d
           AND sender_id <> %d
           AND is_read_buyer = 0",
        $bid_id,
        $uid   // 👈 WICHTIG: current buyer
    )
);

$chat_class = ($has_unread_for_this_bid > 0) ? ' has-unread' : '';
$disabled_attr = $can_write_question ? '' : ' disabled';
/* ===== ACTIONS ===== */
        echo '<td>
    <div class="rqq-actions-pro">

        <button type="button"
            class="rqq-pro-chat rqq-question-toggle'.$chat_class.'"
            data-bid-id="'.$bid_id.'"
            '.$disabled_attr.'>
            Chat
        </button>

        <span class="rqq-pro-sep">·</span>
                <a href="mailto:'.$bidder_email.'" class="rqq-pro-email">
                    Email
                </a>

                '.($tag == 'Open' 
                    ? '<a class="rqq-pro-choose" href="' . home_url() . '/?p_action=choose_winner&pid=' . $bid->pid . '&bid=' . $bid->id . '">
                        Select →
                       </a>' 
                    : '' 
                ).'

            </div>
        </td>';

        echo '</tr>'; // 🔴 ОБЯЗАТЕЛЬНО

        /* ===== QUESTIONS THREAD ===== */
        echo '<tr class="rqq-questions-row" data-bid-id="'.$bid_id.'" style="display:none;">';
        echo '<td colspan="4">';
        echo '<div class="rqq-questions-box">';
        echo '<div class="rqq-questions-list">';

        if(!empty($questions)){
            foreach($questions as $q){
                $is_buyer_msg = ((int)$q->sender_id === $buyer_id);
                echo '<div class="rqq-q-item '.($is_buyer_msg ? 'is-buyer' : 'is-supplier').'">';
                echo '<div class="rqq-q-message">'.nl2br(esc_html($q->message)).'</div>';
                echo '<div class="rqq-q-meta">'.($is_buyer_msg ? 'Buyer' : 'Supplier').' · '.esc_html(mysql2date('d M Y H:i', $q->created_at)).'</div>';
                echo '</div>';
            }
        } else {
            echo '<div class="rqq-q-empty">No questions yet.</div>';
        }

        echo '</div>';

        if($can_write_question){
            echo '<div class="rqq-q-form">';
            echo '<textarea class="rqq-q-input" maxlength="250" placeholder="Write your question (max 250)"></textarea>';
            echo '<div class="rqq-q-form-actions">';
            echo '<span class="rqq-q-counter">0/250</span>';
            echo '<button type="button" class="rqq-q-send" data-bid-id="'.$bid_id.'">Send</button>';
            echo '</div>';
            echo '</div>';
        } elseif(!$is_open_lot){
            echo '<div class="rqq-q-locked">Questions are read-only because RFQ is not Open.</div>';
        }

        echo '</div>';
        echo '</td>';
        echo '</tr>';

        /* ===== DESCRIPTION ===== */
        if (!empty($bid->description)) {

            $desc_raw = trim(wp_strip_all_tags($bid->description));
            $desc_short = mb_substr($desc_raw, 0, 250, 'UTF-8');
            $is_cut = mb_strlen($desc_raw, 'UTF-8') > 250;

            echo '<tr class="rqq-quote-desc">';
            echo '<td colspan="4">
                <div class="rqq-offer-box">
                    <div class="rqq-offer-title">Offer details</div>
                    <div class="rqq-offer-text">'
                        . nl2br(esc_html($desc_short))
                        . ($is_cut ? '…' : '') .
                    '</div>
                </div>
            </td>';
            echo '</tr>';
        }

    }

} else {

    echo '<tr>';
    echo '<td colspan="4" style="text-align:center;">
        No quotes have been received yet.<br>
        You may close this RFQ or continue waiting for quotes.
    </td>';
    echo '</tr>';

}

echo '</tbody></table>';
echo '</td>';
echo '</tr>';
								   

								   
								   
							   }
							  ?>
						  </tbody>
					   </table> 
					   
<style>
.rqq-message-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-left: 6px;
    padding: 2px 7px;
    border-radius: 999px;
    background: #ffa300;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.4;
}
</style>
<script>
jQuery(document).ready(function($){
  
  let rqqFilterTimer;

$(document).on('change', '.status-filter input[type="checkbox"]', function(){

    const $form = $(this).closest('form');

    clearTimeout(rqqFilterTimer);

    $('body').addClass('rqq-loading');

    rqqFilterTimer = setTimeout(function(){
        $form.submit();
    }, 400);

});

/* ===============================
   CLICK FULL REQUEST ROW
=============================== */
$(document).on('click', '.quote-head-row', function(e){

    if ($(e.target).closest('a, button, .view-quotes, .rqq-actions-menu, .rqq-dropdown, .export-icon-xls').length) {
        return;
    }

    $(this).find('.view-quotes').trigger('click');
});
  
    /* ===============================
       VIEW QUOTES
    =============================== */
    $(document).on('click', '.view-quotes', function(e){
        e.preventDefault();

        var $btn = $(this);
        var $row = $btn.closest('tr');
        var $quotes = $row.next('.rqq-quote-row');

        if (!$quotes.length) return;

        var $allRows = $row.nextUntil('tr[data-bid-count]');

       if ($btn.hasClass('open')) {
    $btn.removeClass('open');
    $row.removeClass('is-open');
    $btn.find('.vq-text').text('View');
    $allRows.stop(true, true).slideUp(200);
} else {
    $btn.addClass('open');
    $row.addClass('is-open');
    $btn.find('.vq-text').text('Hide');
    $allRows.stop(true, true).slideDown(200);

            /* remove message badge near View */
            $btn.find('.rqq-message-badge').remove();

            /* restore offers count after reading new quotes */
            var totalCount = $row.data('bid-count');
            var $offers = $row.find('.rqq-offers');

            if ($offers.find('.quote-badge').length) {
                $offers.html(
                    '<span class="quote-count">'+totalCount+'</span>' +
                    ' <span class="rqq-offers-label">Offers</span>'
                );
            }

            /* reset old quote_read logic */
            var project_id = $btn.data('project_id');

            if (project_id) {
                $.post('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
    action: 'reset_quote_read',
    project_id: project_id
});

                /* reset unread messages */
                
            }
        }
    });

   $(document).on('click', '.rqq-question-toggle', function(e){
    e.preventDefault();

    var $btn = $(this);
    if ($btn.is(':disabled')) return;

    var $row = $btn.closest('tr');
    var bidId = parseInt($btn.data('bid-id'), 10);
    var $questionsRow = $row.nextAll('.rqq-questions-row[data-bid-id="' + bidId + '"]').first();

    if (!$questionsRow.length) return;

    $questionsRow.stop(true, true).slideToggle(200);

    var isOpen = $btn.toggleClass('open').hasClass('open');
    $btn.text(isOpen ? 'Hide' : 'Chat');

    /* 🔥 WICHTIG: RESET NUR HIER */
    if (isOpen && $btn.hasClass('has-unread')) {

    $.post('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
        action: 'reset_buyer_question_read_bid',
        nonce: '<?php echo esc_js(wp_create_nonce('reset_buyer_question_read_nonce')); ?>',
        bid_id: bidId
    });

    $btn.removeClass('has-unread');
}
});
    /* ===============================
       SEND MESSAGE
    =============================== */
    $(document).on('input', '.rqq-q-input', function(){
        var val = $(this).val() || '';

        if (val.length > 250) {
            val = val.substring(0, 250);
            $(this).val(val);
        }

        $(this).closest('.rqq-q-form').find('.rqq-q-counter').text(val.length + '/250');
    });

    $(document).on('click', '.rqq-q-send', function(e){
        e.preventDefault();

        var $btn = $(this);
        var bidId = parseInt($btn.data('bid-id'), 10);
        var $box = $btn.closest('.rqq-questions-box');
        var $input = $box.find('.rqq-q-input');
        var message = ($input.val() || '').trim();

        if (!bidId || !message) return;

        $btn.prop('disabled', true);

        $.post('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
            action: 'send_rfq_question',
            nonce: '<?php echo esc_js(wp_create_nonce('send_rfq_question_nonce')); ?>',
            bid_id: bidId,
            message: message
        }).done(function(resp){

            if (!resp || !resp.success) {
                alert(resp && resp.data && resp.data.message ? resp.data.message : 'Failed to send');
                return;
            }

            var data = resp.data || {};
            var safeText = $('<div/>').text(data.message || message).html();
            var safeTime = $('<div/>').text(data.created_at || '').html();

            $box.find('.rqq-q-empty').remove();

            $box.find('.rqq-questions-list').append(
                '<div class="rqq-q-item is-buyer">' +
                    '<div class="rqq-q-message">' + safeText + '</div>' +
                    '<div class="rqq-q-meta">Buyer · ' + safeTime + '</div>' +
                '</div>'
            );

            $input.val('');
            $box.find('.rqq-q-counter').text('0/250');

        }).fail(function(){
            alert('Network error');
        }).always(function(){
            $btn.prop('disabled', false);
        });
    });

    /* ===============================
       DROPDOWN
    =============================== */
    $(document).on('click', '.rqq-dots', function(e){
        e.stopPropagation();

        var $menu = $(this).closest('.rqq-actions-menu');
        $('.rqq-actions-menu').not($menu).removeClass('active');
        $menu.toggleClass('active');
    });

    $(document).on('click', function(){
        $('.rqq-actions-menu').removeClass('active');
    });

    $(document).on('click', '.rqq-dropdown', function(e){
        e.stopPropagation();
    });

});
</script>				   
					   
					   
                   <table class="table d-none table-hover table-outline table-vcenter   card-table">
                     <thead><tr>

                       <th><?php echo __('Project Title','ProjectTheme'); ?></th>
                       <th><?php echo __('Budget','ProjectTheme') ?></th>
                       <th><?php echo __('Date Made','ProjectTheme') ?></th>
                       <th><?php echo __('Quotes','ProjectTheme') ?></th>
                       <th><?php echo __('Options','ProjectTheme') ?></th>

                      </tr></thead><tbody>

                        <?php

                               foreach($r as $row)
                               {



                                   ?>

                                       <tr>
                                             <td><a href="<?php echo get_permalink($row->ID) ?>"><?php echo $row->post_title ?></a><br/>
                                                  <small class="nb-padd"><?php printf(__('You have %s active proposals.','ProjectTheme'), projectTheme_number_of_bid($row->ID)) ?></small>
                                             </td>
                                             <td class='text-success'><?php



                                             $hourly_paid = get_post_meta($row->ID,'hourly_paid',true);
                                             if($hourly_paid == 1)
                                             {
                                                 $hourly_rate = get_post_meta($row->ID,'hourly_rate',true);
                                                 echo projecttheme_get_show_price($hourly_rate) . "/hr";
                                             }
                                             else
                                             echo ProjectTheme_get_budget_name_string_fromID(get_post_meta($row->ID,'budgets',true));

                                              ?></td>
                                             <td><?php echo get_the_date($date_format, $row->ID) ?></td>
                                             <td><?php echo  projectTheme_number_of_bid($row->ID) ?></td>
                                             <td><a href="<?php echo get_the_permalink( $row->ID ); ?>" class='btn btn-outline-primary btn-sm'><?php echo __('View Project','ProjectTheme') ?></a></td>
                                       </tr>
                                   <?php
                               }

                        ?>


                    </tbody>
                   </table>

                   <?php echo $own_pagination->display_pagination(); ?>
                 </div>

                   <?php
             }
             else {

        ?>

       <?php } ?>

     </div>

     <?php } elseif('quotes' == $pg){


       $uid = get_current_user_id();

       $prf = $wpdb->prefix;

       $s = "select SQL_CALC_FOUND_ROWS * from ".$prf."project_bids bids
       INNER JOIN $wpdb->posts posts ON posts.ID=bids.pid
       INNER JOIN ".$prf."postmeta pmeta ON pmeta.post_id=bids.pid
       WHERE posts.post_author='$uid' AND pmeta.meta_value='0' and pmeta.meta_key='winner' and exists(select * from $wpdb->users users where users.ID=bids.uid) order by bids.id desc limit $offset, $amount_per_page";


       $r = $wpdb->get_results($s);




       $total_rows   = projecttheme_get_last_found_rows();
       $own_pagination = new own_pagination($amount_per_page, $total_rows, ProjectTheme_get_project_link_with_page($pgid, 'quotes'). "&");

?>

   <div class="card" style="border-top:0">

     <?php

           if(count($r) > 0)
           {
                 ?>
                 <div class="p-3 table-responsive">
                 <table class="table table-hover table-outline table-vcenter   card-table">
                   <thead><tr>

                     <th><?php echo __('Project Title','ProjectTheme'); ?></th>
                     <th><?php echo __('Provider','ProjectTheme'); ?></th>
                     <th><?php echo __('Quote','ProjectTheme') ?></th>
                     <th><?php echo __('Date Made','ProjectTheme') ?></th>
                     <th><?php echo __('Timeframe','ProjectTheme') ?></th>
                     <th><?php echo __('Options','ProjectTheme') ?></th>

                    </tr></thead><tbody>

                      <?php

                             foreach($r as $row)
                             {

                                        $provider = get_userdata($row->uid);
                                        $pid = $row->pid;
                                 ?>

                                     <tr>
                                           <td><a href="<?php echo get_permalink($pid) ?>"><?php echo $row->post_title ?></a></td>
                                           <td><a href="<?php echo get_permalink($pid) ?>"><?php echo $provider->user_login ?></a></td>
                                           <td class='text-success'><?php

                                           $hourly_paid = get_post_meta($row->ID,'hourly_paid',true);
                                           if($hourly_paid == 1)
                                           {
                                                echo projectTheme_get_show_price($row->bid, 0)."/hr";
                                           }
                                           else

                                           echo projectTheme_get_show_price($row->bid, 0); ?></td>
                                           <td><?php echo get_the_date($date_format, $row->datemade) ?></td>
                                           <td><?php echo  sprintf(__('%s day(s)','ProjectTheme'), $row->days_done) ?></td>
                                           <td><a href="<?php echo get_the_permalink( $row->ID ); ?>" class='btn btn-outline-primary btn-sm'><?php echo __('View Project','ProjectTheme') ?></a>
                                           <a href="<?php echo get_the_permalink( $row->ID ); ?>" class='btn btn-outline-success btn-sm'><?php echo __('Choose Winner','ProjectTheme') ?></a></td>
                                     </tr>
                                 <?php
                             }

                      ?>


                  </tbody>
                 </table> <?php echo $own_pagination->display_pagination(); ?> </div>

                 <?php
           }
           else {

      ?>


       <div class="p-3">
         <?php _e('You do not have any active quotes.','ProjectTheme') ?>
       </div>

     <?php } ?>

</div>

<?php }elseif('pending' == $pg)
{
  $uid = get_current_user_id();

  $prf = $wpdb->prefix;
  $s = "select SQL_CALC_FOUND_ROWS * from ".$prf."project_orders orders where orders.buyer='$uid' and order_status='0' order by id desc limit $offset, $amount_per_page";
  $r = $wpdb->get_results($s);

  $total_rows   = projecttheme_get_last_found_rows();
  $own_pagination = new own_pagination($amount_per_page, $total_rows, ProjectTheme_get_project_link_with_page($pgid, 'pending'). "&");


?>

<div class="card" style="border-top:0">

<?php

      if(count($r) > 0)
      {
            ?>
            <div class="p-3 table-responsive">
            <table class="table table-hover table-outline table-vcenter   card-table">
              <thead><tr>

                <th><?php echo __('Project Title','ProjectTheme'); ?></th>
                <th><?php echo __('Provider','ProjectTheme'); ?></th>
                <th><?php echo __('Price','ProjectTheme') ?></th>
                <th><?php echo __('Date Made','ProjectTheme') ?></th>
                <th><?php echo __('Completion','ProjectTheme') ?></th>
                <th><?php echo __('Options','ProjectTheme') ?></th>

               </tr></thead><tbody>

                 <?php

                 $now = current_time('timestamp');

                        foreach($r as $row)
                        {

                                   $provider  = get_userdata($row->freelancer);
                                   $pst       = get_post($row->pid);

                            ?>

                                <tr>
                                      <td><p class="mb-2"><a href="<?php echo get_permalink($pst->ID) ?>"><?php echo $pst->post_title ?></a></p>
                                        <?php



                                              echo '<div class="alert alert-secondary alert-smaller-padding"><small>' . __('Waiting for the freelancer to deliver work.','ProjectTheme') . '</small></div>';
                                              pt_show_buyer_payment_status($row);
                                              pt_freelancer_area_timing_status_customer($row);
                                         ?>

                                      </td>
                                      <td><a href="<?php echo get_permalink($row->ID) ?>"><?php echo $provider->user_login ?></a></td>
                                      <td class='text-success'><?php echo projectTheme_get_show_price($row->order_total_amount, 0) ?></td>
                                      <td><?php echo date_i18n($date_format, $row->datemade) ?></td>
                                      <td <?php if($row->completion_date < $now) echo 'class="text-danger"'; ?>><?php echo  date_i18n($date_format, $row->completion_date) ?></td>
                                      <td><a href="<?php echo projecttheme_get_workspace_link_from_project_id( $row->pid ); ?>" class='btn btn-outline-primary btn-sm'><?php echo __('Workspace','ProjectTheme') ?></a></td>
                                </tr>
                            <?php
                        }

                 ?>


             </tbody>
            </table> <?php echo $own_pagination->display_pagination(); ?>  </div>

            <?php
      }
      else {

 ?>


  <div class="p-3">
    <?php _e('You do not have any active projects.','ProjectTheme') ?>
  </div>

<?php } ?>
</div>

<?php }elseif($pg == 'delivered'){


  $prf = $wpdb->prefix;
  $s = "select SQL_CALC_FOUND_ROWS * from ".$prf."project_orders orders where orders.buyer='$uid' and order_status='1' order by id='desc' limit $offset, $amount_per_page";
  $r = $wpdb->get_results($s);

  $total_rows   = projecttheme_get_last_found_rows();
  $own_pagination = new own_pagination($amount_per_page, $total_rows, ProjectTheme_get_project_link_with_page($pgid, 'delivered'). "&");



?>

<div class="card" style="border-top:0">

<?php

      if(count($r) > 0)
      {
            ?>
            <div class="p-3 table-responsive">
            <table class="table table-hover table-outline table-vcenter   card-table">
              <thead><tr>

                <th><?php echo __('Project Title','ProjectTheme'); ?></th>
                <th><?php echo __('Provider','ProjectTheme'); ?></th>
                <th><?php echo __('Price','ProjectTheme') ?></th>
                <th><?php echo __('Date Made','ProjectTheme') ?></th>
                <th><?php echo __('Completed On','ProjectTheme') ?></th>
                <th><?php echo __('Options','ProjectTheme') ?></th>

               </tr></thead><tbody>

                 <?php

                 $now = current_time('timestamp');

                        foreach($r as $row)
                        {

                                   $provider  = get_userdata($row->freelancer);
                                   $pst       = get_post($row->pid);

                            ?>

                                <tr>
                                      <td><p class="mb-1"><a href="<?php echo get_permalink($row->ID) ?>"><?php echo $pst->post_title ?></a></p>
                                        <?php


                                            pt_show_buyer_payment_status($row);


                                         ?>

                                      </td>
                                      <td><a href="<?php echo get_permalink($row->ID) ?>"><?php echo $provider->user_login ?></a></td>
                                      <td class='text-success'><?php echo projectTheme_get_show_price($row->order_total_amount, 0) ?></td>
                                      <td><?php echo date_i18n($date_format, $row->datemade) ?></td>
                                      <td><?php echo  date_i18n($date_format, $row->marked_done_freelancer) ?></td>
                                      <td>
                                        <div class="dropdown z1x1x2"> <span class="noti-noti x1x2z3">1</span>
                                          <button class="btn btn-secondary dropdown-toggle dropdown-functions-settings" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-cog"></i></button>
                                                    <div class="dropdown-menu" id="options-thing-sale" aria-labelledby="dropdownMenuButton">
                                                        <a class="dropdown-item" href="<?php echo projecttheme_get_workspace_link_from_project_id( $pst->ID ); ?>"><?php echo __('Workspace','ProjectTheme') ?> <span class="noti-noti">1</span></a>

                              <a class="dropdown-item" href="<?php echo get_site_url(); ?>/?p_action=mark_completed&id=<?php echo $row->id; ?>"><?php echo __('Mark Completed','ProjectTheme') ?></a>
                                    </div>
                                  </div>

                                </tr>
                            <?php
                        }

                 ?>


             </tbody>
           </table> <?php echo $own_pagination->display_pagination(); ?>  </div>

            <?php
      }
      else {

 ?>


  <div class="p-3">
    <?php _e('You do not have any active projects.','ProjectTheme') ?>
  </div>

<?php } ?>
</div>

<?php }elseif($pg == 'cancelled'){

  $prf = $wpdb->prefix;
  $s = "select SQL_CALC_FOUND_ROWS * from ".$prf."project_orders orders where orders.buyer='$uid' and order_status='3' order by id='desc' limit $offset, $amount_per_page";
  $r = $wpdb->get_results($s);

  $total_rows   = projecttheme_get_last_found_rows();
  $own_pagination = new own_pagination($amount_per_page, $total_rows, ProjectTheme_get_project_link_with_page($pgid, 'cancelled'). "&");



?>

<div class="card" style="border-top:0">

<?php

      if(count($r) > 0)
      {
            ?>
            <div class="p-3 table-responsive">
            <table class="table table-hover table-outline table-vcenter   card-table">
              <thead><tr>

                <th><?php echo __('Project Title','ProjectTheme'); ?></th>
                <th><?php echo __('Provider','ProjectTheme'); ?></th>
                <th><?php echo __('Price','ProjectTheme') ?></th>
                <th><?php echo __('Date Started','ProjectTheme') ?></th>
                <th><?php echo __('Cancelled On','ProjectTheme') ?></th>
                <th><?php echo __('Options','ProjectTheme') ?></th>

               </tr></thead><tbody>

                 <?php

                 $now = current_time('timestamp');

                        foreach($r as $row)
                        {

                                   $provider  = get_userdata($row->freelancer);
                                   $pst       = get_post($row->pid);

                            ?>

                                <tr>
                                      <td><a href="<?php echo get_permalink($row->ID) ?>"><?php echo $pst->post_title ?></a></td>
                                      <td><a href="<?php echo get_permalink($row->ID) ?>"><?php echo $provider->user_login ?></a></td>
                                      <td class='text-success'><?php echo projectTheme_get_show_price($row->order_total_amount, 0) ?></td>
                                      <td><?php echo date_i18n($date_format, $row->datemade) ?></td>
                                      <td><?php echo  date_i18n($date_format, $row->cancelled_date) ?></td>
                                      <td>-</td>
                                </tr>
                            <?php
                        }

                 ?>


             </tbody>
            </table> </div>

            <?php
      }
      else {

 ?>


  <div class="p-3">
    <?php _e('You do not have any cancelled projects.','ProjectTheme') ?>
  </div>

<?php } ?>

</div>
  <?php }elseif($pg == 'completed'){


  $prf = $wpdb->prefix;
  $s = "select SQL_CALC_FOUND_ROWS * from ".$prf."project_orders orders where orders.buyer='$uid' and order_status='2' order by id='desc' limit $offset, $amount_per_page";
  $r = $wpdb->get_results($s);

  $total_rows   = projecttheme_get_last_found_rows();
  $own_pagination = new own_pagination($amount_per_page, $total_rows, ProjectTheme_get_project_link_with_page($pgid, 'completed'). "&");



?>

<div class="card" style="border-top:0">

<?php

      if(count($r) > 0)
      {
            ?>
            <div class="p-3 table-responsive">
            <table class="table table-hover table-outline table-vcenter   card-table">
              <thead><tr>

                <th><?php echo __('Project Title','ProjectTheme'); ?></th>
                <th><?php echo __('Provider','ProjectTheme'); ?></th>
                <th><?php echo __('Price','ProjectTheme') ?></th>
                <th><?php echo __('Date Started','ProjectTheme') ?></th>
                <th><?php echo __('Accepted On','ProjectTheme') ?></th>
                <th><?php echo __('Options','ProjectTheme') ?></th>

               </tr></thead><tbody>

                 <?php

                 $now = current_time('timestamp');
                 $pgid_payments = get_option('ProjectTheme_my_account_payments_id');

                        foreach($r as $row)
                        {

                                   $provider  = get_userdata($row->freelancer);
                                   $pst       = get_post($row->pid);

                            ?>

                                <tr>
                                      <td><a href="<?php echo get_permalink($row->pid) ?>"><?php echo $pst->post_title ?></a></td>
                                      <td><a href="<?php echo ProjectTheme_get_user_profile_link($provider->ID) ?>"><?php echo $provider->user_login ?></a></td>
                                      <td class='text-success'><?php echo projectTheme_get_show_price($row->order_total_amount, 0) ?></td>
                                      <td><?php echo date_i18n($date_format, $row->datemade) ?></td>
                                      <td><?php echo  date_i18n($date_format, $row->marked_done_buyer) ?></td>
                                      <td>
                                          <?php

                                          $order = new project_orders($row->id);

                                          if(!$order->is_escrow_released())
                                          {
                                                  ?>

                                                        <a href="<?php echo ProjectTheme_get_project_link_with_page($pgid_payments, 'releaseescrow', '&id=' . $row->id) ?>" class="btn btn-outline-success btn-sm"><?php _e('Release Escrow','ProjectTheme'); ?></a>

                                                  <?php
                                          }

                                           ?>

                                        <a href="<?php echo projecttheme_get_workspace_link_from_project_id( $pst->ID ); ?>" class="btn btn-outline-primary btn-sm"><?php _e('Workspace','ProjectTheme'); ?></a></td>
                                </tr>
                            <?php
                        }

                 ?>


             </tbody>
            </table><?php echo $own_pagination->display_pagination(); ?>  </div>

            <?php
      }
      else {

 ?>


  <div class="p-3">
    <?php _e('You do not have any active projects.','ProjectTheme') ?>
  </div>

<?php } ?>

</div>

<?php }  elseif($pg == 'unpublished'){

  $prf = $wpdb->prefix;
  $s = "select SQL_CALC_FOUND_ROWS * from ".$prf."postmeta pmeta, ".$prf."posts posts where posts.ID=pmeta.post_id and posts.post_type='project' and
  posts.post_status='draft' and posts.post_author='$uid' and pmeta.meta_key='closed' and pmeta.meta_value='0' order by posts.ID desc limit $offset, $amount_per_page";
  $r = $wpdb->get_results($s);

  $total_rows   = projecttheme_get_last_found_rows();
  $own_pagination = new own_pagination($amount_per_page, $total_rows, ProjectTheme_get_project_link_with_page($pgid, 'home'). "&");



  ?>

  <div class="card" style="border-top:0">

  <?php

  if(count($r) > 0)
  {
        ?>
        <div class="p-3 table-responsive">
        <table class="table table-hover table-outline table-vcenter   card-table">
          <thead><tr>

            <th><?php echo __('Project Title','ProjectTheme'); ?></th>
            <th><?php echo __('Budget','ProjectTheme') ?></th>
            <th><?php echo __('Date Made','ProjectTheme') ?></th>
            <th><?php echo __('Quotes','ProjectTheme') ?></th>
            <th><?php echo __('Options','ProjectTheme') ?></th>

           </tr></thead><tbody>

             <?php

                    foreach($r as $row)
                    {



                        ?>

                            <tr>
                                  <td><a href="<?php echo get_permalink($row->ID) ?>"><?php echo $row->post_title ?></a></td>
                                  <td class='text-success'><?php echo ProjectTheme_get_budget_name_string_fromID(get_post_meta($row->ID, 'budgets', true)) ?></td>
                                  <td><?php echo get_the_date($date_format, $row->ID) ?></td>
                                  <td><?php echo  projectTheme_number_of_bid($row->ID) ?></td>
                                  <td><a href="<?php echo ProjectTheme_post_new_with_pid_stuff_thg($row->ID, '4'); ?>" class='btn btn-outline-primary btn-sm'><?php echo __('Publish','ProjectTheme') ?></a>
                                  <a href="<?php echo get_site_url() ?>/?p_action=delete_project&pid=<?php echo $row->ID; ?>" class='btn btn-outline-danger btn-sm'><?php echo __('Delete','ProjectTheme') ?></a></td>
                            </tr>
                        <?php
                    }

             ?>


         </tbody>
        </table>

        <?php echo $own_pagination->display_pagination(); ?>
      </div>

        <?php
  }
  else {

  ?>


  <div class="p-3">
  <?php _e('You do not have any unpublished projects.','ProjectTheme') ?>
  </div>

  <?php } ?>



<?php } ?> </div>

        </div></div> <!-- end dif content -->





		<?php get_template_part('lib/my_account/footer-area-account') ?>
</div></div>

<?php


      $page = ob_get_contents();
      ob_end_clean();
      return $page;

}



 ?>
