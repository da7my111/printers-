document.querySelectorAll('.delete').forEach(btn => {
    btn.addEventListener('click', async function(e) {
        e.preventDefault();
        const printerId = this.getAttribute('data-id');
        const row = this.closest('tr');
        
        if (!confirm('هل أنت متأكد من حذف هذه الطابعة؟')) return;
        
        try {
            const response = await fetch('delete-printer.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ id: printerId })
            });
            
            const data = await response.json();
            
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'فشل في حذف الطابعة');
            }
            
            // إضافة تأثير للحذف
            row.style.transition = 'all 0.3s';
            row.style.opacity = '0';
            setTimeout(() => row.remove(), 300);
            
        } catch (error) {
            console.error('Error:', error);
            alert(error.message);
        }
    });
});