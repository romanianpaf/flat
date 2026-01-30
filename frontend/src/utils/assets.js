/**
 * Helper pentru încărcarea asset-urilor în Vite
 * Înlocuiește require() din Vue CLI
 */

// Import static pentru imaginile comune
import loadingGif from '@/assets/img/loading.gif'
import placeholderImg from '@/assets/img/placeholder.jpg'
import bruceMarsImg from '@/assets/img/bruce-mars.jpg'

// Background images
import curved6 from '@/assets/img/curved-images/curved6.jpg'
import curved9 from '@/assets/img/curved-images/curved9.jpg'
import curved11 from '@/assets/img/curved-images/curved11.jpg'
import curved14 from '@/assets/img/curved-images/curved14.jpg'
import atriaFundal from '@/assets/img/curved-images/atria-fundal.jpeg'
import bgSmartHome1 from '@/assets/img/bg-smart-home-1.jpg'
import bgSmartHome2 from '@/assets/img/bg-smart-home-2.jpg'

// Export named
export const loading = loadingGif
export const placeholder = placeholderImg
export const defaultAvatar = bruceMarsImg

// Funcție pentru a obține URL-ul unei imagini
export function getImageUrl(name) {
  // Map pentru imagini cunoscute
  const images = {
    'loading.gif': loadingGif,
    'placeholder.jpg': placeholderImg,
    'bruce-mars.jpg': bruceMarsImg,
    'curved6.jpg': curved6,
    'curved9.jpg': curved9,
    'curved11.jpg': curved11,
    'curved14.jpg': curved14,
    'atria-fundal.jpeg': atriaFundal,
    'bg-smart-home-1.jpg': bgSmartHome1,
    'bg-smart-home-2.jpg': bgSmartHome2,
  }
  
  return images[name] || placeholder
}

// Export object cu toate imaginile
export default {
  loading: loadingGif,
  placeholder: placeholderImg,
  defaultAvatar: bruceMarsImg,
  curved6,
  curved9,
  curved11,
  curved14,
  atriaFundal,
  bgSmartHome1,
  bgSmartHome2,
  getImageUrl
}
