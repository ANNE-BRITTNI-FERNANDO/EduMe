import './bootstrap';
import Alpine from 'alpinejs';
import { addToCart, removeFromCart } from './cart';

window.Alpine = Alpine;
Alpine.start();

// Make cart functions available globally
window.addToCart = addToCart;
window.removeFromCart = removeFromCart;
