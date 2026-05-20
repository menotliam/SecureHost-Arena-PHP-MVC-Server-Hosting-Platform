<?php
class Pagination {
    protected $total;
    protected $perPage;
    protected $current;
    protected $baseUrl;

    public function __construct($total, $perPage = 10, $current = 1, $baseUrl = ''){
        $this->total = (int)$total;
        $this->perPage = (int)$perPage;
        $this->current = max(1, (int)$current);
        $this->baseUrl = $baseUrl;
    }

    public function getTotalPages(){
        return (int)ceil($this->total / $this->perPage);
    }

    public function getOffset(){
        return ($this->current - 1) * $this->perPage;
    }

    public function getLimit(){
        return $this->perPage;
    }

    public function createLinks($adjacents = 2){
        $last = $this->getTotalPages();
        if($last <= 1) return '';

        $html = '<nav class="pagination" aria-label="Page navigation"><ul class="pagination-list">';

        // Previous
        if($this->current > 1){
            $html .= '<li class="page-item"><a class="page-link" href="' . $this->pageUrl($this->current - 1) . '">Prev</a></li>';
        }

        // Pages
        $start = max(1, $this->current - $adjacents);
        $end = min($last, $this->current + $adjacents);

        if($start > 1){
            $html .= '<li class="page-item"><a class="page-link" href="' . $this->pageUrl(1) . '">1</a></li>';
            if($start > 2) $html .= '<li class="page-item"><span class="page-ellipsis">...</span></li>';
        }

        for($i = $start; $i <= $end; $i++){
            if($i == $this->current){
                $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
            } else {
                $html .= '<li class="page-item"><a class="page-link" href="' . $this->pageUrl($i) . '">' . $i . '</a></li>';
            }
        }

        if($end < $last){
            if($end < $last - 1) $html .= '<li class="page-item"><span class="page-ellipsis">...</span></li>';
            $html .= '<li class="page-item"><a class="page-link" href="' . $this->pageUrl($last) . '">' . $last . '</a></li>';
        }

        // Next
        if($this->current < $last){
            $html .= '<li class="page-item"><a class="page-link" href="' . $this->pageUrl($this->current + 1) . '">Next</a></li>';
        }

        $html .= '</ul></nav>';
        return $html;
    }

    protected function pageUrl($page){
        $sep = (strpos($this->baseUrl, '?') === false) ? '?' : '&';
        return $this->baseUrl . $sep . 'page=' . $page;
    }
}
