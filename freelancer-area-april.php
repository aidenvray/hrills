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
    $uid = (int)$current_user->ID;

    get_template_part('lib/my_account/aside-menu');
?>

<div class="page-wrapper">
<div class="container">

<h1 class="page-title">My Quotes</h1>

<?php
$status = $_GET['status'] ?? [];
if (!is_array($status)) {
    $status = [$status];
}

$prf = $wpdb->prefix;
$q_table = $prf . 'rfq_questions';

$r = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT *
         FROM {$prf}project_bids bids
         INNER JOIN {$prf}posts posts ON posts.ID = bids.pid
         WHERE bids.uid = %d
         ORDER BY bids.id DESC",
        $uid
    )
);
if(!is_array($r)) $r = [];
?>

<form method="GET" class="status-filter">
  <p>Status:</p>

  <label class="rqq-tag">
    <input type="checkbox" name="status[]" value="Open" <?php echo in_array('Open', $status, true) ? 'checked' : ''; ?>>
    Open
  </label>

  <label class="rqq-tag">
    <input type="checkbox" name="status[]" value="Closed" <?php echo in_array('Closed', $status, true) ? 'checked' : ''; ?>>
    Closed
  </label>

  <label class="rqq-tag">
    <input type="checkbox" name="status[]" value="Won" <?php echo in_array('Won', $status, true) ? 'checked' : ''; ?>>
    Won
  </label>

  <label class="rqq-tag">
    <input type="checkbox" name="status[]" value="Unread" <?php echo in_array('Unread', $status, true) ? 'checked' : ''; ?>>
    Unread
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
  <th>Quote</th>
  <th></th>
</tr>
</thead>

<tbody>
<?php
$count = 0;

foreach($r as $row):

    $pid      = (int)$row->pid;
    $bid_user = (int)$row->uid;
    $bid_id   = (int)$row->id;

    $parts         = get_post_meta($pid, 'parts', true);
    $machine_model = get_post_meta($pid, 'machine_model', true);
    $manufacturer  = get_post_meta($pid, 'manufacturer', true);
    $made_me_date  = get_post_meta($pid, 'made_me_date', true);

    $winner   = get_post_meta($pid, 'winner', true);
    $closed   = get_post_meta($pid, 'closed', true);
    $buyer_id = (int)get_post_field('post_author', $pid);

    if($closed == 1 && empty($winner)){
        $tag = 'Closed';
    } elseif(empty($winner)){
        $tag = 'Open';
    } elseif((string)$winner === (string)$bid_user){
        $tag = 'Won';
    } else {
        $tag = 'Not selected';
    }

    $questions = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT id, sender_id, message, created_at 
             FROM {$q_table} 
             WHERE bid_id=%d 
             ORDER BY id ASC",
            $bid_id
        )
    );
    if(!is_array($questions)) $questions = [];

    /* unread = buyer messages for supplier */
    $unread_count = 0;
    foreach($questions as $q){
        if((int)$q->sender_id === $buyer_id){
            $unread_count++;
        }
    }

    if(in_array('Unread', $status, true) && $unread_count === 0){
        continue;
    }

    if(!empty($status)){
        $normal_status_filter = array_diff($status, ['Unread']);
        if(!empty($normal_status_filter) && !in_array($tag, $normal_status_filter, true)){
            continue;
        }
    }

    $count++;

    $parts_text = '';
    if(is_array($parts) && count($parts) > 1){
        $extra = count($parts) - 1;
        $parts_text = '+ '.$extra.' part'.($extra > 1 ? 's' : '');
    }

    $price = ($row->bid !== null && $row->bid !== '') 
        ? number_format((int)$row->bid, 0, '', ' ') . ' $' 
        : '—';

    $attachments = get_posts([
        'post_type'   => 'attachment',
        'post_parent' => $pid,
        'post_author' => $bid_user,
        'meta_key'    => 'is_bidding_file',
        'meta_value'  => '1'
    ]);

    $first_sender_id = !empty($questions) ? (int)$questions[0]->sender_id : 0;
    $is_open_lot = ($tag === 'Open');
    $can_supplier_write = ($uid === $bid_user && $is_open_lot && $first_sender_id === $buyer_id);
?>

<tr>
<td>
  <div style="font-weight:600;"><?php echo esc_html($manufacturer); ?></div>
  <a href="<?php echo esc_url(get_permalink($pid)); ?>" class="rqq-id-block">#MB<?php echo $pid; ?></a>
</td>

<td>
  <?php echo esc_html($machine_model); ?><br>
  <b><?php echo (is_array($parts) && isset($parts[0]['name'])) ? esc_html($parts[0]['name']) : ''; ?></b><br>
  <span style="color:#6b7280; font-size:13px;"><?php echo esc_html($parts_text); ?></span>
</td>

<td class="rqq-quote-cell">
  <div class="rqq-price"><?php echo esc_html($price); ?></div>
  <div class="rqq-status rqq-status-<?php echo esc_attr(strtolower(str_replace(' ', '-', $tag))); ?>">
    <?php echo esc_html($tag); ?>
  </div>
</td>

<td style="text-align:right;">
  <span class="view-quotes">
    <svg class="vq-icon" width="12" height="12" viewBox="0 0 12 12">
      <path d="M3 4 L6 8 L9 4" stroke="#f59e0b" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>

    <span class="vq-text">View</span>

    <?php if($unread_count > 0): ?>
      <span class="rqq-unread-badge">+<?php echo (int)$unread_count; ?></span>
    <?php endif; ?>
  </span>
</td>
</tr>

<tr class="rqq-quote-row" style="display:none;">
<td colspan="4">
<div class="quote-details">

<div class="quote-row">
  <div><?php echo $made_me_date ? date('d.m.Y',(int)$made_me_date) : ''; ?></div>
  <div>Delivery time: <?php echo (int)$row->days_done; ?> Days</div>

  <div>
    <?php
    $pdf_found = false;

    if(!empty($attachments)){
        foreach($attachments as $att){
            $url  = wp_get_attachment_url($att->ID);
            $mime = get_post_mime_type($att->ID);

            if($mime === 'application/pdf'){
                echo '<a href="'.esc_url($url).'" target="_blank" class="rqq-pdf-btn">📄 View PDF</a>';
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

  <div class="quote-actions">
    <?php if($tag == 'Open'): ?>
      <div class="rqq-actions-menu">
        <span class="rqq-dots">⋯</span>
        <div class="rqq-dropdown">
          <a href="<?php echo esc_url(get_permalink($pid)); ?>">Edit Quote</a>
          <a class="rqq-danger" href="<?php echo esc_url(home_url('/?withdraw_quote=1&pid='.$pid.'&bid='.$row->id)); ?>">Withdraw</a>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="quote-questions" data-bid-id="<?php echo $bid_id; ?>">
  <div class="rqq-offer-title">Questions from buyer</div>

  <div class="rqq-questions-list">
    <?php if(!empty($questions)): ?>
      <?php foreach($questions as $q): 
        $is_buyer = ((int)$q->sender_id === $buyer_id);
      ?>
        <div class="rqq-q-item <?php echo $is_buyer ? 'is-buyer' : 'is-supplier'; ?>">
          <div class="rqq-q-message"><?php echo nl2br(esc_html($q->message)); ?></div>
          <div class="rqq-q-meta">
            <?php echo $is_buyer ? 'Buyer' : 'Supplier'; ?> · <?php echo esc_html(mysql2date('d M Y H:i', $q->created_at)); ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="rqq-q-empty">No questions yet.</div>
    <?php endif; ?>
  </div>

  <?php if($can_supplier_write): ?>
    <div class="rqq-q-form">
      <textarea class="rqq-q-input" maxlength="250" placeholder="Write your reply (max 250)"></textarea>
      <div class="rqq-q-form-actions">
        <span class="rqq-q-counter">0/250</span>
        <button type="button" class="rqq-q-send" data-bid-id="<?php echo $bid_id; ?>">Reply</button>
      </div>
    </div>
  <?php elseif(!$is_open_lot): ?>
    <div class="rqq-q-locked">Questions are read-only because RFQ is not Open.</div>
  <?php endif; ?>
</div>

<?php if(!empty($row->description)): ?>
  <div class="quote-description">
    <?php echo nl2br(esc_html(wp_strip_all_tags($row->description))); ?>
  </div>
<?php endif; ?>

</div>
</td>
</tr>

<?php endforeach; ?>

<?php
if($count == 0){
    echo '<tr><td colspan="4">No results</td></tr>';
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

    $(document).on('click','.view-quotes',function(e){
        e.preventDefault();

        let btn = $(this);
        let row = btn.closest('tr');
        let details = row.next('.rqq-quote-row');

        $('.rqq-quote-row').not(details).slideUp();
        $('.view-quotes').not(btn).removeClass('open').find('.vq-text').text('View');

        if(btn.hasClass('open')){
            btn.removeClass('open');
            btn.find('.vq-text').text('View');
            details.slideUp();
        } else {
            btn.addClass('open');
            btn.find('.vq-text').text('Hide');
            details.slideDown();

            /* remove unread badge visually */
            btn.find('.rqq-unread-badge').remove();
        }
    });

    $(document).on('input', '.rqq-q-input', function(){
        var v = $(this).val() || '';
        if(v.length > 250){
            v = v.substring(0,250);
            $(this).val(v);
        }
        $(this).closest('.rqq-q-form').find('.rqq-q-counter').text(v.length + '/250');
    });

    $(document).on('click', '.rqq-q-send', function(e){
        e.preventDefault();

        var $btn = $(this);
        var bidId = parseInt($btn.data('bid-id'),10);
        var $box = $btn.closest('.quote-questions');
        var $input = $box.find('.rqq-q-input');
        var message = ($input.val() || '').trim();

        if(!bidId || !message) return;

        $btn.prop('disabled', true);

        $.post('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
            action: 'send_rfq_question',
            nonce: '<?php echo esc_js(wp_create_nonce('send_rfq_question_nonce')); ?>',
            bid_id: bidId,
            message: message
        }).done(function(resp){

            if(!resp || !resp.success){
                alert(resp && resp.data && resp.data.message ? resp.data.message : 'Failed to send');
                return;
            }

            var data = resp.data || {};
            var safeText = $('<div/>').text(data.message || message).html();
            var safeTime = $('<div/>').text(data.created_at || '').html();

            $box.find('.rqq-q-empty').remove();

            $box.find('.rqq-questions-list').append(
                '<div class="rqq-q-item is-supplier">' +
                    '<div class="rqq-q-message">' + safeText + '</div>' +
                    '<div class="rqq-q-meta">Supplier · ' + safeTime + '</div>' +
                '</div>'
            );

            $input.val('');
            $box.find('.rqq-q-counter').text('0/250');

        }).always(function(){
            $btn.prop('disabled', false);
        });
    });

    $(document).on('click', '.rqq-dots', function(e){
        e.stopPropagation();

        let menu = $(this).closest('.rqq-actions-menu');
        $('.rqq-actions-menu').not(menu).removeClass('active left');
        menu.toggleClass('active');
    });

    $(document).on('click', function(){
        $('.rqq-actions-menu').removeClass('active left');
    });

    $(document).on('click', '.rqq-dropdown', function(e){
        e.stopPropagation();
    });

});
</script>

<?php
    return ob_get_clean();
}

