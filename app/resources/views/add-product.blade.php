<!DOCTYPE html>
<form method="POST" action="/add-product">
    @csrf
    <input type="text" name="title" placeholder="Product Title" required>
    <textarea name="description" placeholder="Description" required></textarea>
    <button type="submit">Add Product</button>
</form>
