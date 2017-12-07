/**
 * Created by liuyang on 2017/4/7.
 */
$(function() {
    /**
     * 当选择agency name是,列表自动刷新
     */
    $('#thagencybusinesssearch-company_id').change(function() {
        $('#agency-business-search').submit();
    })

})