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
function pt_freelancer_area_timing_status($row)
{
  $now = current_time( 'timestamp' );
  if($row->completion_date < $now)
  {
      echo '<div class="alert alert-danger alert-smaller-padding"><small class="">'.sprintf(__('You have gone past the deadline with this project.','ProjectTheme')) . '</small></div>';
  }

}

//*******************************************************
//
//      freelancer area
//
//*******************************************************

function pt_freelancer_area_payment_status($row)
{
  $date_format = get_option('date_format');

  $ProjectTheme_payment_model = get_option('ProjectTheme_payment_model');
  if($ProjectTheme_payment_model == "ewallet_only")
  {


        $order = new project_orders($row->id);

        if($order->has_escrow_deposited() == false)
        {
            echo '<div class="alert alert-warning alert-smaller-padding"><small class="">'.sprintf(__('Waiting the customer to deposit escrow.','ProjectTheme')) . '</small></div>';
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

      echo '<div class="alert alert-warning alert-smaller-padding"><small class="">
      '.sprintf(__('The payment for this project is done outside of the website.','ProjectTheme')) . '</small></div>';
  }
  else {
    // code...


    $order = new project_orders($row->id);

    if($order->has_marketplace_payment_been_deposited() == false)
    {
        echo '<div class="alert alert-warning alert-smaller-padding"><small class="">'.sprintf(__('This project has not been paid by the customer.','ProjectTheme')) . '</small></div>';
    }
    else {

      $obj = $order->get_marketplace_payment_object();
      echo '<div class="alert alert-success alert-smaller-padding"><small class="">'.sprintf(__('Payment was sent on %s.','ProjectTheme'), date_i18n($date_format, $obj->datemade)) . '</small></div>';

    }
  }

  do_action('pt_on_freelancer_payment_status', $row);

}


//*******************************************************
//
//      freelancer area
//
//*******************************************************

function project_theme_my_account_freelancer_area_fnc()
{
    ob_start();

    global $wpdb, $current_user;
    $current_user = wp_get_current_user();
    $uid = $current_user->ID;

    get_template_part('lib/my_account/aside-menu');
?>

<div class="page-wrapper">
<div class="container">

<h1 class="page-title">My Quotes</h1>

<?php
$status = $_GET['status'] ?? [];

$prf = $wpdb->prefix;

$r = $wpdb->get_results("
    SELECT * 
    FROM {$prf}project_bids bids
    INNER JOIN {$prf}posts posts ON posts.ID = bids.pid
    WHERE bids.uid = '{$uid}'
    ORDER BY bids.id DESC
");
?>

<form method="GET" class="status-filter">
  <p>Status:</p>

  <label class="rqq-tag">
    <input type="checkbox" name="status[]" value="Open"
      <?php echo (isset($_GET['status']) && in_array('Open', $_GET['status'])) ? 'checked' : '' ?>>
    Open
  </label>

  <label class="rqq-tag">
    <input type="checkbox" name="status[]" value="Closed"
      <?php echo (isset($_GET['status']) && in_array('Closed', $_GET['status'])) ? 'checked' : '' ?>>
    Closed
  </label>

  <label class="rqq-tag">
    <input type="checkbox" name="status[]" value="Won"
      <?php echo (isset($_GET['status']) && in_array('Won', $_GET['status'])) ? 'checked' : '' ?>>
    Won
  </label>

  <button class="rqq-submit">Apply</button>
</form>

<div class="card">
<div class="table-responsive">

<table class="rqq-table">

<thead>
<tr>
    <th>Request</th>
    <th>Details</th>
    <th>My Quote</th>
    <th>Status</th>
    <th></th>
</tr>
</thead>

<tbody>

<?php
$count = 0;

foreach($r as $row):

    $pid      = $row->pid;
    $bid_user = $row->uid;

    $parts         = get_post_meta($pid, 'parts', true);
    $machine_model = get_post_meta($pid, 'machine_model', true);
    $manufacturer  = get_post_meta($pid, 'manufacturer', true);
    $made_me_date  = get_post_meta($pid, 'made_me_date', true);

    $winner = get_post_meta($pid, 'winner', true);
    $closed = get_post_meta($pid, 'closed', true);

    // ===== STATUS =====
    if($closed == 1 && empty($winner)){
        $tag = 'Closed';
    } elseif(empty($winner)){
        $tag = 'Open';
    } elseif($winner == $bid_user){
        $tag = 'Won';
    } else {
        $tag = 'Not selected';
    }

    if(empty($status) || in_array($tag, $status)):

    $count++;

    // ===== PARTS FIX =====
    $parts_text = '';
    if(is_array($parts) && count($parts) > 1){
        $extra = count($parts) - 1;
        $parts_text = '+ '.$extra.' part'.($extra > 1 ? 's' : '');
    }

    // ===== PRICE FORMAT =====
    $price = ($row->bid !== null && $row->bid !== '')
    ? number_format((int)$row->bid, 0, '', ' ') . ' $'
    : '—';

    // ===== FILE =====
    $attachments = get_posts([
        'post_type'   => 'attachment',
        'post_parent' => $pid,
        'post_author' => $bid_user,
        'meta_key'    => 'is_bidding_file',
        'meta_value'  => '1'
    ]);
?>

<!-- ================= MAIN ROW ================= -->

<tr>

<td>

    <div style="font-weight:600;">
        <?php echo esc_html($manufacturer); ?>
    </div>

    <a href="<?php echo get_permalink($pid); ?>" class="rqq-id-block">
        #MB<?php echo $pid; ?>
    </a>

</td>

<td>

    <?php echo esc_html($machine_model); ?><br>

    <b>
    <?php echo isset($parts[0]['name'])
        ? esc_html($parts[0]['name'])
        : ''; ?>
    </b><br>

    <span style="color:#6b7280; font-size:13px;">
        <?php echo $parts_text; ?>
    </span>

</td>

<td>
    <b><?php echo $price; ?></b>
</td>

<td>
    <span class="rqq-tag"><?php echo $tag; ?></span>
</td>

<td style="text-align:right;">

    <span class="view-quotes">

        <svg class="vq-icon" width="12" height="12" viewBox="0 0 12 12">
            <path d="M3 4 L6 8 L9 4"
                stroke="#f59e0b"
                stroke-width="1.8"
                fill="none"
                stroke-linecap="round"
                stroke-linejoin="round"/>
        </svg>

        <span class="vq-text">View</span>

    </span>

</td>

</tr>

<!-- ================= DETAILS ================= -->

<tr class="rqq-quote-row" style="display:none;">
<td colspan="5">

<div class="quote-details">

<div class="quote-row">

<!-- DATE -->
<div>
<?php echo $made_me_date ? date('d.m.Y',$made_me_date) : ''; ?>
</div>

<!-- DELIVERY -->
<div>
Delivery time: <?php echo intval($row->days_done); ?> Days
</div>

<!-- FILE -->
<div>

<?php
$pdf_found = false;

if(!empty($attachments)){
    foreach($attachments as $att){
        $url = wp_get_attachment_url($att->ID);
        $mime = get_post_mime_type($att->ID);

        if($mime === 'application/pdf'){
            echo '<a href="'.$url.'" target="_blank" class="rqq-pdf-btn">📄 View PDF</a>';
            $pdf_found = true;
            break;
        }
    }
}

if(!$pdf_found){
    echo '<span class="rqq-no-file">No Files</span>';
}
?>

</div>

<!-- ACTIONS -->
<div class="quote-actions">

<?php
$project_author_id = get_post_field('post_author', $pid);

// 🔥 CHECK: existiert Conversation?
$has_conversation = rfq_has_conversation($pid, $project_author_id, $bid_user);

// 👉 NUR anzeigen wenn Chat existiert
if($has_conversation){
    echo '<span class="open-message-modal rqq-chat"
        data-rfq="'.$pid.'"
        data-supplier="'.$project_author_id.'">
        Chat
    </span>';
}
?>

<?php if($tag == 'Open'): ?>
<div class="rqq-actions-menu">
    <span class="rqq-dots">⋯</span>

    <div class="rqq-dropdown">
        <a href="<?php echo get_permalink($pid); ?>">Edit Quote</a>

        <a class="rqq-danger"
           href="<?php echo home_url(); ?>/?withdraw_quote=1&pid='.$pid.'&bid='.$row->id.'">
           Withdraw
        </a>
    </div>
</div>
<?php endif; ?>

</div>

</div>

<!-- DESCRIPTION -->
<?php if(!empty($row->description)): ?>
<div class="quote-description">
<?php echo nl2br(esc_html(wp_strip_all_tags($row->description))); ?>
</div>
<?php endif; ?>

</div>

</td>
</tr>

<?php
endif;
endforeach;

if($count == 0){
    echo '<tr><td colspan="5">No results</td></tr>';
}
?>

</tbody>
</table>

</div>
</div>

</div>
</div>


<script>
jQuery(function($){

    // VIEW / HIDE
    $(document).on('click','.view-quotes',function(e){

        e.preventDefault();

        let btn = $(this);
        let row = btn.closest('tr');
        let details = row.next('.rqq-quote-row');

        $('.rqq-quote-row').not(details).slideUp();
        $('.view-quotes').not(btn)
            .removeClass('open')
            .find('.vq-text').text('View');

        if(btn.hasClass('open')){
            btn.removeClass('open');
            btn.find('.vq-text').text('View');
            details.slideUp();
        } else {
            btn.addClass('open');
            btn.find('.vq-text').text('Hide');
            details.slideDown();
        }

    });

    // OPEN DROPDOWN
    $(document).on('click', '.rqq-dots', function(e){
        e.stopPropagation();

        let menu = $(this).closest('.rqq-actions-menu');
        let dropdown = menu.find('.rqq-dropdown');

        $('.rqq-actions-menu').not(menu).removeClass('active left');

        menu.toggleClass('active');

        setTimeout(function(){

            let rect = dropdown[0].getBoundingClientRect();

            if(rect.right > window.innerWidth){
                menu.addClass('left');
            } else {
                menu.removeClass('left');
            }

        }, 10);

    });

    // CLOSE ON OUTSIDE CLICK
    $(document).on('click', function(){
        $('.rqq-actions-menu').removeClass('active left');
    });

    // CLICK INSIDE
    $(document).on('click', '.rqq-dropdown', function(e){
        e.stopPropagation();
    });

});
</script>

<?php
    return ob_get_clean();
}				   
