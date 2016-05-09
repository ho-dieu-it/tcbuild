<?php
/**
 * Created by PhpStorm.
 * User: hpdv4
 * Date: 26/11/2015
 * Time: 21:56
 */

namespace AppBundle\Utils;


class Paginator{

    protected $currentpage;

    protected $limit;

    protected $numpages;

    protected $itemscount;

    protected $offset;

    protected $pageNum;

    public function __construct($itemscount, $pageNum, $limit) {
        $this->pageNum = $pageNum;

        $this->limit = $limit;

        //set total items count from controller
        $this->itemscount = $itemscount;
        //get params from request URL
        $this->getParamsFromRequest();
        //Calculate number of pages total
        $this->getNumPages();
        //Calculate first shown item on current page
        $this->calculateOffset();
    }

    private function getParamsFromRequest() {
        //If current page number is set in URL
        //if (!isset($this->pageNum)) {
            //else set default page to render
            $this->currentpage = $this->pageNum;
        //}
        //If limit is defined in URL
       // if (!isset($this->limit)) {
            //else set default limit to 20
          //  $this->limit = 2;
        //}
        //If currentpage is set to null or is set to 0 or less
        //set it to default (1)
        if (($this->currentpage == null) || ($this->currentpage < 1)) {
            $this->currentpage = 1;
        }
        //if limit is set to null set it to default (10)
        if (($this->limit == null)) {
            $this->limit = 10;
            //if limit is any number less than 1 then set it to 0 for displaying
            //items without limit
        } else if ($this->limit < 1) {
            $this->limit = 0;
        }
    }

    public function getNumPages() {
        //If limit is set to 0 or set to number bigger then total items count
        //display all in one page
        if (($this->limit < 1) || ($this->limit > $this->itemscount)) {
            $this->numpages = 1;
        } else {
            //Calculate rest numbers from dividing operation so we can add one
            //more page for this items
            $restItemsNum = $this->itemscount % $this->limit;
            //if rest items > 0 then add one more page else just divide items
            //by limit
            $restItemsNum > 0 ? $this->numpages = intval($this->itemscount / $this->limit) + 1 : $this->numpages = intval($this->itemscount / $this->limit);
        }
        return $this->numpages;
    }

    private function calculateOffset() {
        //Calculet offset for items based on current page number
        $this->offset = ($this->currentpage - 1) * $this->limit;
    }

    //For using from controller
    public function getLimit() {
        return $this->limit;
    }
    //For using from controller
    public function getOffset() {
        return $this->offset;
    }

    public function RenderPagination() {
        $html = '';
        //Insert all in one div tag
        $html.= '<div>';
        //We need this form for sumbitting limit into URL via GET call
        $html.='<form id= "paginator" name="paginator" method="get" action="#" >';
        //When limit is changed - just submit form
        $html.= '<select name="limit" onchange="javascript:document.forms.paginator.submit()">';
        $html.= '<option value="10" ';
        if ($this->limit == 10) {
            $html.='selected';
        } $html.='>10</option>';
        $html.= '<option value="20" ';
        if ($this->limit == 20) {
            $html.='selected';
        } $html.='>20</option>';
        $html.= '<option value="30" ';
        if ($this->limit == 30) {
            $html.='selected';
        } $html.='>30</option>';
        $html.= '<option value="50" ';
        if ($this->limit == 50) {
            $html.='selected';
        } $html.='>50</option>';
        $html.= '<option value="100" ';
        if ($this->limit == 100) {
            $html.='selected';
        } $html.='>100</option>';
        $html.= '<option value="500" ';
        if ($this->limit == 500) {
            $html.='selected';
        } $html.='>500</option>';
        $html.= '<option value="0" ';
        if ($this->limit == 0) {
            $html.='selected';
        } $html.='>All</option>';
        $html.='</select>';
        $html.='</form>';
        $html.='<ul>';

        //Generate links for pages
        for ($i = 1; $i < $this->numpages + 1; $i++) {

            $html.='<li><a href="' . $_SERVER['PHP_SELF'] . '?limit=' . $this->limit . '&amp;page=' . $i . '">' . $i . '</a></li>';
        }
        $html.='</ul>';
        $html.= '</div>';

        return $html;
    }

}