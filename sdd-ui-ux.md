# System Design Document: Frontend Visual & Interactive Engine

## 1. Aesthetic Specifications (Cyber-Glass Theme)
* **Background Framework:** Deep dark slate/navy hue (`#071954`).
* **Typography:** Primary font for all text is 'Montserrat' (via Google Fonts), with system fonts as fallback.
* **Glassmorphism Compound:** 
```css
  background: rgba(7, 25, 84, 0.45);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
```
* **Interactive Highlights:** High-contrast neon yellow (#facc15). Active focus states, pinned markers, text links, and primary execution assets must leverage this accent color.

## 2. UI Features
* **Edit Mode Toggle:** Switch between view-only mode (default) and edit mode (shows edit/delete controls
* **Category Card Layout:** Categories displayed in responsive grid (1/2/3 columns based on screen size
* **Collapsible Categories:** All categories and subcategories start collapsed; click to expand/collapse
* **Pin Indicator:** Pinned categories show 📌 icon
* **Modals for Editing:** Modal dialogs for editing categories and bookmarks
* **Responsive Design:** Works on mobile, tablet, and desktop devices