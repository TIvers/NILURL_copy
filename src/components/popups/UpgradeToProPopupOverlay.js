import React, { useRef } from 'react';
import './upgradeToProPopup.css';

const UpgradeToProPopupOverlay = ({ onClose, children }) => {
  const popupRef = useRef(null);

  const handleClickOutside = (event) => {
    if (popupRef.current && !popupRef.current.contains(event.target)) {
      onClose();
    }
  };

  return (
   // <div className="popup-overlay" onClick={handleClickOutside}>
      <div className="popup-container" ref={popupRef} onClick={handleClickOutside}>
        {children}
      </div>
   // </div>
  );
};

export default UpgradeToProPopupOverlay;