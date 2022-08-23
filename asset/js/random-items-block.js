'use strict';

class RandomItemsBlock {

  constructor(itemIds, randIdx, totalImages, basePath, siteSlug, linkToItems = false, blockUuid, btnPlacement, headerLevel) {
    this.API_PATH = "api/items";
    this.itemIds = itemIds;
    this.randIdx = randIdx;
    this.basePath = basePath;
    this.siteSlug = siteSlug;
    this.totalImages = totalImages;
    this.linkToItems = linkToItems;
    this.btnPlacement = btnPlacement;
    this.itemPath = this.basePath + "/s/" + this.siteSlug + "/item/";
    this.media_api_path = this.basePath + '/api/media/';
    this.headerLevel = headerLevel;
    this.spinner = '<div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>';
    this.spinnerClass = '.lds-ellipsis';
  }

  getNewRandomItems(el, btn) {
    const self = this
    el.html(this.spinner);
    setTimeout(function(){
      for (let i = 1; i <= self.totalImages; i++) {
        self.randIdx = (self.randIdx + i) % self.itemIds.length;
        self.getRandomItem(el, btn)
      }
    }, 400, el, btn);

  }

  getRandomItem(el, btn) {
    const self = this;
    $.ajax({
      url: self.basePath + "/" + self.API_PATH + "/" + self.itemIds[self.randIdx],
      targetEl: el,
      btnEl: btn,
      success: function(data) {
          self.parseApiData(data, this.targetEl, this.btnEl);
          $(self.spinnerClass).remove()
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
    const imageMediaUrl = typeof(data['o:media']) != 'undefined' ? data['o:media'][0]['@id'] : null
    const descriptionMediaUrl = typeof(data['o:media']) != 'undefined' && typeof(data['o:media'][1]) != 'undefined' ? data['o:media'][1]['@id'] : null

    if (itemImageUrl != null && itemTitle != null) {
      this.renderApiData(itemId, itemImageUrl, itemTitle, itemDescription, imageMediaUrl, descriptionMediaUrl, targetEl, btnEl);
    }
    else {
      this.randIdx = (this.randIdx + 1) % this.itemIds.length;
      this.getRandomItem(targetEl, btnEl);
    }
  }

  renderApiData(itemId, itemImageUrl, itemTitle, itemDescription, imageMediaUrl, descriptionMediaUrl, targetEl, btnEl) {
    const self = this;
    let altText = '';
    let descriptionHtml = null;

    if (descriptionMediaUrl != null) {
      $.when(
        $.get(imageMediaUrl, function(data) {
          altText = typeof(data['o:alt_text']) != 'undefined' && data['o:alt_text'] != null ? data['o:alt_text'] : '';
        }),
        $.get(descriptionMediaUrl, function(data) {
          descriptionHtml = typeof(data['data']) != 'undefined' && data['data']['html'] != '' ? data['data']['html'] : null;
        })
      )
      .done(function() {
        self.assembleHtml(itemId, itemImageUrl, itemTitle, itemDescription, altText, descriptionHtml, targetEl, btnEl);
      })
    }
    else {
      $.when(
        $.get(this.imageMediaUrl, function(data) {
          altText = typeof(data['o:alt_text']) != 'undefined' && data['o:alt_text'] != null ? data['o:alt_text'] : '';
        })
      )
      .done(function() {
        self.assembleHtml(itemId, itemImageUrl, itemTitle, itemDescription, altText, descriptionHtml, targetEl, btnEl);
      })
    }
  }

  assembleHtml(itemId, itemImageUrl, itemTitle, itemDescription, altText, descriptionHtml, targetEl, btnEl) {
    const self = this;
    let html = '<div class="random-item">';
    if (this.linkToItems == 1) {
      html += '<' + this.headerLevel + '><a href="' + this.itemPath + itemId + '">' + itemTitle + '</a></' + this.headerLevel + '>';
      html += '<div class="random-item-image">';
      html += '<a href="' + this.itemPath + itemId + '"><img src="' + itemImageUrl + '" alt="' + altText + '"></a>';
      html += '</div>';
      html += '<div class="random-item-description">';
    }
    else {
      html += '<' + this.headerLevel + '>' + itemTitle + '</' + this.headerLevel + '>';
      html += '<div class="random-item-image">';
      html += '<img src="' + itemImageUrl + '" alt="' + altText + '">';
      html += '</div>';
      html += '<div class="random-item-description">';
    }

    if (descriptionHtml != null) {
      html += descriptionHtml;
    }
    else if (itemDescription != null) {
      html += '<p>' + itemDescription + '</p>';
    }

    if (self.btnPlacement == 1) {
      html += btnEl.outerHTML;
    }

    html += '</div>';
    html += '</div>';

    targetEl.append(html);
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
