<div class="section-header">
    <div>
        <h2 class="section-title">Sitemap và robots</h2>
        <p class="section-note">Kiểm soát cách công cụ tìm kiếm thu thập dữ liệu website.</p>
    </div>
</div>
<input type="hidden" name="seo_indexing" value="0">
<div class="form-group">
    <label class="form-label">
        <input type="checkbox" name="seo_indexing" value="1" @checked(old('seo_indexing', $settings['seo_indexing']))>
        Cho phép công cụ tìm kiếm lập chỉ mục website
    </label>
    <div class="form-help">Tắt tùy chọn này trên website thử nghiệm hoặc khi chưa muốn website xuất hiện trên Google.</div>
</div>
<div class="form-group">
    <label class="form-label">Đường dẫn được phép trong robots.txt</label>
    <textarea class="form-control-custom textarea" name="robots_allow" rows="5">{{ old('robots_allow', $settings['robots_allow']) }}</textarea>
    <div class="form-help">Mỗi dòng là một rule Allow, dùng để cho bot tải trang, CSS, JavaScript và hình ảnh.</div>
</div>
<div class="form-group">
    <label class="form-label">Đường dẫn chặn trong robots.txt</label>
    <textarea class="form-control-custom textarea" name="robots_disallow" rows="4" placeholder="/admin/&#10;/login">{{ old('robots_disallow', $settings['robots_disallow']) }}</textarea>
    <div class="form-help">Mỗi dòng là một đường dẫn bắt đầu bằng dấu /. Không dùng robots.txt để bảo vệ dữ liệu nhạy cảm.</div>
</div>
<div class="form-help">
    Kiểm tra trực tiếp:
    <a href="{{ route('sitemap') }}" target="_blank" rel="noopener">sitemap.xml</a>
    ·
    <a href="{{ route('robots') }}" target="_blank" rel="noopener">robots.txt</a>
</div>
