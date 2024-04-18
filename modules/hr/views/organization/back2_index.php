<style>
    body {
  margin-bottom: 6em;
}

$border-color: #e3e5ef;
$primary-color: #bada55;

.treeview {
  //Variables
  $_treeview-level-size: 55px;
  //General styles for bootstrap
  .btn-default {
    border-color: $border-color;

    &:hover {
      background-color: #f7faea;
      color: $primary-color;
    }
  }
  // Targetting the HTML Elements for treeview
  ul {
    list-style: none;
    padding-left: 32px;

    li {
      padding: 50px 0px 0px 35px;
      position: relative;

      &:before {
        content: "";
        position: absolute;
        top: -26px;
        left: -31px;
        border-left: 2px dashed #a2a5b5;
        width: 1px;
        height: 100%;
      }

      &:after {
        content: "";
        position: absolute;
        border-top: 2px dashed #a2a5b5;
        top: 70px;
        left: -30px;
        width: 65px;
      }

      &:last-child:before {
        top: -22px;
        height: 90px;
      }
    }
  }
  // First Level Elements
  & > ul {
  & > li {
    &:after,
    &:last-child:before {
      content: unset;
    }

    &:before {
      top: 90px;
      left: 36px;
    }

    &:not(:last-child) > ul > li {
      &:before {
        content: unset;
      }
    }

    & > .treeview__level {
      &:before {
        height: $_treeview-level-size + 5;
        width: $_treeview-level-size + 5;
        top: -9.5px;
        background-color: #54a6d9;
        border: 7.5px solid #d5e9f6;
        font-size: 22px;
      }
    }
    
    & > ul {
      padding-left: 34px;
    }
  }
  
}
  // Treeview Components
  &__level {
    padding: 7px;
    padding-left: ($_treeview-level-size / 2) + 15;
    display: inline-block;
    border-radius: 5px;
    font-weight: 700;
    border: 1px solid $border-color;
    position: relative;
    z-index: 1;

    &:before {
      content: attr(data-level);
      position: absolute;
      left: -($_treeview-level-size / 2);
      top: -6.5px;
      display: flex;
      align-items: center;
      justify-content: center;
      height: $_treeview-level-size;
      width: $_treeview-level-size;
      border-radius: 50%;
      border: 7.5px solid #eef6d5;
      background-color: $primary-color;
      color: #fff;
      font-size: 20px;
    }

    &-btns {
      margin-left: 15px;
      display: inline-block;
      position: relative;
    }

    .level {
      &-same,
      &-sub {
        position: absolute;
        display: none;
        transition: opacity 250ms cubic-bezier(0.7, 0, 0.3, 1);
        &.in {
          display: block;
          .btn-default {
            background-color: #faeaea;
            color: #da5555;
          }
        }
      }

      &-same {
        top: 0;
        left: 45px;
      }
      &-sub {
        top: 42px;
        left: 0px;
      }
      &-remove {
        display: none;
      }
    }

    &.selected {
      background-color: #f9f9fb;
      box-shadow: 0px 3px 15px 0px rgba(0, 0, 0, 0.10);

      .level {
        &-remove {
          display: inline-block;
        }
        &-add {
          display: none;
        }
        &-same,
        &-sub {
          display: none;
        }
      }
    }
  }

  .level-title {
    cursor: pointer;
    user-select: none;
    
    &:hover {
      text-decoration: underline;
    }
  }

  &--mapview {
    ul {
      justify-content: center;
      display: flex;

      li {
        &:before {
          content: unset;
        }

        &:after {
          content: unset;
        }
      }
    }
  }
}

</style>
<div class="treeview js-treeview">
  <ul>
    <li>
      <div class="treeview__level" data-level="A">
        <span class="level-title">Level A</span>
        <div class="treeview__level-btns">
          <div class="btn btn-default btn-sm level-add"><span class="fa fa-plus"></span></div>
          <div class="btn btn-default btn-sm level-remove"><span class="fa fa-trash text-danger"></span></div>
          <div class="btn btn-default btn-sm level-same"><span>Add Same Level</span></div>
          <div class="btn btn-default btn-sm level-sub"><span>Add Sub Level</span></div>
        </div>
      </div>
      <ul>

        <li>
          <div class="treeview__level" data-level="B">
            <span class="level-title">Level A</span>
            <div class="treeview__level-btns">
              <div class="btn btn-default btn-sm level-add"><span class="fa fa-plus"></span></div>
              <div class="btn btn-default btn-sm level-remove"><span class="fa fa-trash text-danger"></span></div>
              <div class="btn btn-default btn-sm level-same"><span>Add Same Level</span></div>
              <div class="btn btn-default btn-sm level-sub"><span>Add Sub Level</span></div>
            </div>
          </div>
          <ul>

            <li>
              <div class="treeview__level" data-level="C">
                <span class="level-title">Level A</span>
                <div class="treeview__level-btns">
                  <div class="btn btn-default btn-sm level-add"><span class="fa fa-plus"></span></div>
                  <div class="btn btn-default btn-sm level-remove"><span class="fa fa-trash text-danger"></span></div>
                  <div class="btn btn-default btn-sm level-same"><span>Add Same Level</span></div>
                  <div class="btn btn-default btn-sm level-sub"><span>Add Sub Level</span></div>
                </div>
              </div>
              <ul>

                <li>
                  <div class="treeview__level" data-level="D">
                    <span class="level-title">Level A</span>
                    <div class="treeview__level-btns">
                      <div class="btn btn-default btn-sm level-add"><span class="fa fa-plus"></span></div>
                      <div class="btn btn-default btn-sm level-remove"><span class="fa fa-trash text-danger"></span></div>
                      <div class="btn btn-default btn-sm level-same"><span>Add Same Level</span></div>
                      <div class="btn btn-default btn-sm level-sub"><span>Add Sub Level</span></div>
                    </div>
                  </div>
                  <ul>
                  </ul>
                </li>
              </ul>
            </li>

            <li>
              <div class="treeview__level" data-level="C">
                <span class="level-title">Level A</span>
                <div class="treeview__level-btns">
                  <div class="btn btn-default btn-sm level-add"><span class="fa fa-plus"></span></div>
                  <div class="btn btn-default btn-sm level-remove"><span class="fa fa-trash text-danger"></span></div>
                  <div class="btn btn-default btn-sm level-same"><span>Add Same Level</span></div>
                  <div class="btn btn-default btn-sm level-sub"><span>Add Sub Level</span></div>
                </div>
              </div>
              <ul>
              </ul>
            </li>

            <li>
              <div class="treeview__level" data-level="C">
                <span class="level-title">Level A</span>
                <div class="treeview__level-btns">
                  <div class="btn btn-default btn-sm level-add"><span class="fa fa-plus"></span></div>
                  <div class="btn btn-default btn-sm level-remove"><span class="fa fa-trash text-danger"></span></div>
                  <div class="btn btn-default btn-sm level-same"><span>Add Same Level</span></div>
                  <div class="btn btn-default btn-sm level-sub"><span>Add Sub Level</span></div>
                </div>
              </div>
              <ul>
              </ul>
            </li>
          </ul>
        </li>

        <li>
          <div class="treeview__level" data-level="B">
            <span class="level-title">Level A</span>
            <div class="treeview__level-btns">
              <div class="btn btn-default btn-sm level-add"><span class="fa fa-plus"></span></div>
              <div class="btn btn-default btn-sm level-remove"><span class="fa fa-trash text-danger"></span></div>
              <div class="btn btn-default btn-sm level-same"><span>Add Same Level</span></div>
              <div class="btn btn-default btn-sm level-sub"><span>Add Sub Level</span></div>
            </div>
          </div>
          <ul>
          </ul>
        </li>
      </ul>
    </li>
  </ul>
</div>
</div>

<template id="levelMarkup">
    <li>
      <div class="treeview__level" data-level="A">
        <span class="level-title">Level A</span>
        <div class="treeview__level-btns">
          <div class="btn btn-default btn-sm level-add"><span class="fa fa-plus"></span></div>
          <div class="btn btn-default btn-sm level-remove"><span class="fa fa-trash text-danger"></span></div>
          <div class="btn btn-default btn-sm level-same"><span>Add Same Level</span></div>
          <div class="btn btn-default btn-sm level-sub"><span>Add Sub Level</span></div>
        </div>
      </div>
      <ul>
      </ul>
</li>
</template>

<?php
$js = <<< JS

$(function() {
  let treeview = {
    resetBtnToggle: function() {
      $(".js-treeview")
        .find(".level-add")
        .find("span")
        .removeClass()
        .addClass("fa fa-plus");
      $(".js-treeview")
        .find(".level-add")
        .siblings()
        .removeClass("in");
    },
    addSameLevel: function(target) {
      let ulElm = target.closest("ul");
      let sameLevelCodeASCII = target
        .closest("[data-level]")
        .attr("data-level")
        .charCodeAt(0);
      ulElm.append($("#levelMarkup").html());
      ulElm
        .children("li:last-child")
        .find("[data-level]")
        .attr("data-level", String.fromCharCode(sameLevelCodeASCII));
    },
    addSubLevel: function(target) {
      let liElm = target.closest("li");
      let nextLevelCodeASCII = liElm.find("[data-level]").attr("data-level").charCodeAt(0) + 1;
      liElm.children("ul").append($("#levelMarkup").html());
      liElm.children("ul").find("[data-level]")
        .attr("data-level", String.fromCharCode(nextLevelCodeASCII));
    },
    removeLevel: function(target) {
      target.closest("li").remove();
      
    }
  };

  // Treeview Functions
  $(".js-treeview").on("click", ".level-add", function() {
    $(this).find("span").toggleClass("fa-plus").toggleClass("fa-times text-danger");
    $(this).siblings().toggleClass("in");
  });

  // Add same level
  $(".js-treeview").on("click", ".level-same", function() {
    treeview.addSameLevel($(this));
    treeview.resetBtnToggle();
  });

  // Add sub level
  $(".js-treeview").on("click", ".level-sub", function() {
    treeview.addSubLevel($(this));
    treeview.resetBtnToggle();
  });
    // Remove Level
  $(".js-treeview").on("click", ".level-remove", function() {
    treeview.removeLevel($(this));
  }); 

  // Selected Level
  $(".js-treeview").on("click", ".level-title", function() {
    let isSelected = $(this).closest("[data-level]").hasClass("selected");
    !isSelected && $(this).closest(".js-treeview").find("[data-level]").removeClass("selected");
    $(this).closest("[data-level]").toggleClass("selected");
  }); 
});



JS;
$this->registerJS($js);
?>