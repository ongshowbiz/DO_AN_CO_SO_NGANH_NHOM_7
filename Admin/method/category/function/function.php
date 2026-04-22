<?php  
function generate_pagination($total_items, $items_per_page = 10, 
        $current_page = 1, $base_url = 'index.php', $query_params = [])
{
    // Xác thực trang hiện tại
    $current_page = (int)$current_page;
    if ($current_page < 1) { 
        $current_page = 1;
    }

    $total_pages = 1; 
    if ($total_items > 0) {
        $total_pages = ceil($total_items / $items_per_page);
    }
    
    if ($current_page > $total_pages) { 
        $current_page = $total_pages;
    }

    $offset = ($current_page - 1) * $items_per_page;
    if ($offset < 0) $offset = 0; // Đảm bảo offset không âm

    $pagination_html = '';

    if ($total_pages > 1) {
        $pagination_html .= '<nav><ul class="pagination justify-content-center m-0">';
        
        // Giữ lại các tham số filter cũ, chỉ thay đổi tham số 'p'
        unset($query_params['p']);

        // Nút "Trang trước" 
        $prev_class = ($current_page <= 1) ? 'disabled' : '';
        $query_params['p'] = ($current_page > 1) ? $current_page - 1 : 1;
        $prev_link = $base_url . '?' . http_build_query($query_params);
        $pagination_html .= "<li class='page-item {$prev_class}'>
            <a class='page-link' href='{$prev_link}'>&laquo;</a></li>";

        // Hiển thị các số trang
        for ($i = 1; $i <= $total_pages; $i++) {
            $active_class = ($i == $current_page) ? 'active' : '';
            $query_params['p'] = $i;
            $page_link = $base_url . '?' . http_build_query($query_params);
            $pagination_html .= "<li class='page-item {$active_class}'>
                    <a class='page-link' href='{$page_link}'>{$i}</a></li>";
        }

        // Nút "Trang sau"
        $next_class = ($current_page >= $total_pages) ? 'disabled' : '';
        $query_params['p'] = ($current_page < $total_pages) ? $current_page + 1 : $total_pages;
        $next_link = $base_url . '?' . http_build_query($query_params);
        $pagination_html .= "<li class='page-item {$next_class}'><a class='page-link' href='{$next_link}'>&raquo;</a></li>";
        
        $pagination_html .= '</ul></nav>';
    }
    
    return [
        'html' => $pagination_html,
        'offset' => $offset,
        'limit' => (int)$items_per_page,
        'total_pages' => $total_pages,
        'current_page' => $current_page
    ];
}
?>