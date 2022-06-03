'use strict';

class RandomItemsBlock {

  constructor(itemIds, randIdx, totalImages, basePath, siteSlug, linkToItems = false, blockUuid, btnPlacement) {
    this.API_PATH = "api/items";
    this.itemIds = itemIds;
    this.randIdx = randIdx;
    this.basePath = basePath;
    this.siteSlug = siteSlug;
    this.totalImages = totalImages;
    this.linkToItems = linkToItems;
    this.btnPlacement = btnPlacement;
    this.itemPath = this.basePath + "/s/" + this.siteSlug + "/item/";
  }

  getNewRandomItems(el, btn) {
    el.html('');
    for (let i = 1; i <= this.totalImages; i++) {
      this.randIdx = (this.randIdx + i) % this.itemIds.length;
      this.getRandomItem(el, btn)
    }
  }

  getRandomItem(el, btn) {
    const self = this;
    $.ajax({
      url: self.basePath + "/" + self.API_PATH + "/" + self.itemIds[self.randIdx],
      targetEl: el,
      btnEl: btn,
      success: function(data) {
          self.parseApiData(data, this.targetEl, this.btnEl);
        }
      }
    );
  }

  // FIXME: Can we pass the description field in so we can look at something other than dcterms:description?
  parseApiData(data, targetEl, btnEl) {
    const itemId = data['o:id'];
    const itemImageUrl = typeof(data['thumbnail_display_urls']['large']) != 'undefined' ? data['thumbnail_display_urls']['large'] : null;
    const itemTitle = typeof(data['o:title']) != 'undefined' ? data['o:title'] : null;
    const itemDescription = typeof(data['dcterms:description']) != 'undefined' ? data['dcterms:description'][0]['@value'] : null;
    const mediaUrl = typeof (data['o:media']) != 'undefined' ? data['o:media'][0]['@id'] : null;
    if (itemImageUrl != null && itemTitle != null && itemDescription != null && mediaUrl != null) {
      this.renderApiData(itemId, itemImageUrl, itemTitle, itemDescription, mediaUrl, targetEl, btnEl);
    }
    else {
      this.randIdx = (this.randIdx + 1) % this.itemIds.length;
      this.getRandomItem(targetEl, btnEl);
    }
  }

  renderApiData(itemId, itemImageUrl, itemTitle, itemDescription, mediaUrl, targetEl, btnEl) {
    const self = this;
    let html = '';
    let altText = '';

    $.get(mediaUrl, function(data) {
      altText = typeof(data['o:alt_text']) != 'undefined' && data['o:alt_text'] != null ? data['o:alt_text'] : '';
    }).always(function(){
      html += '<div class="random-item">';
      if (this.linkToItems == 1) {
        html += '<div class="random-item-image">';
        html += '<a href="' + this.itemPath + itemId + '"><img src="' + itemImageUrl + '" alt="' + altText + '"></a>';
        html += '</div>';
        html += '<div class="random-item-description">';
        html += '<h3><a href="' + this.itemPath + itemId + '">' + itemTitle + '</a></h3>';
      }
      else {
        html += '<div class="random-item-image">';
        html += '<img src="' + itemImageUrl + '" alt="' + altText + '">';
        html += '</div>';
        html += '<div class="random-item-description">';
        html += '<h3>' + itemTitle + '</h3>';
      }
      html += '<p>' + itemDescription + '</p>';
      if (self.btnPlacement == 1) {
        console.log(btnEl)
        html += btnEl.outerHTML;
      }
      html += '</div>';
      html += '</div>';
      targetEl.append(html);
    });
  }
}

(function($) {
    $(document).ready(function() {
      $('body').on('click', '.random-items-block-switch', function(e) {
        e.preventDefault();
        const btnHtml = $(this).parent()[0];
        const targetId = $(this).data('random-block-target');
        const targetEl = $('#' + targetId).children('.item.resource');
        randomItemsBlock.getNewRandomItems(targetEl, btnHtml);
      })
    });
})(jQuery);
