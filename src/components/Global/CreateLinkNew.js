import React, { useState, useEffect, useRef } from "react";
import "../../styles/Global/CreateLinkNew.css";
import { useNavigate } from 'react-router-dom';
import CreatingLink from "../creating-link/CreatingLink";
import Overlay from "../creating-link/Overlay";
import { usePremium } from '../../LogicComp/DataProvider';
import { PRICEPAGE_ROUTE } from "../../LogicComp/utils/Const";

const CreateLinkNew = ({ linkCount, availableLinks }) => {
  const [flag, setFlag] = useState(false);
  const [popupMessage, setPopupMessage] = useState(""); 
  const [isPopupVisible, setPopupVisibility] = useState(false); 
  const [isButtonDisabled, setButtonDisabled] = useState(false); 
  const { isPremium } = usePremium();
  const buttonRef = useRef(null);
  const popupRef = useRef(null);
  const navigate = useNavigate();

  useEffect(() => {
    let limit = 0;
    if (isPremium === "free") {
      limit = 10;
    } else if (isPremium === "base") {
      limit = 100;
    } else if (isPremium === "premium") {
      limit = 1000;
    }
    console.log(linkCount);
    console.log(availableLinks);
    if (linkCount === null || availableLinks === null || linkCount >= limit || availableLinks <= 0) {
      setButtonDisabled(true);
    } else {
      setButtonDisabled(false);
    }
  }, [linkCount, availableLinks, isPremium]);

  const handleClosePopup = () => {
    setPopupVisibility(false);
    setPopupMessage("");
  };

  const handleMouseEnter = () => {
    if (isButtonDisabled) {
      setPopupMessage(
        `Ваше количество ссылок достигло максимума для ${isPremium === "free" ? "Бесплатной подписки" : isPremium === "base" ? "Базовой подписки" : "Премиум подписки"}, для большего количества ссылок приобретите более высокий тарифный план.`
      );
      setPopupVisibility(true);
    }
  };

  const handleMouseLeave = (event) => {
    const button = buttonRef.current;
    const popup = popupRef.current;
    if (button && popup) {
      if (!button.contains(event.relatedTarget) && !popup.contains(event.relatedTarget)) {
        handleClosePopup();
      }
    }
  };

  const handleClick = () => {
    if (!isButtonDisabled) {
      setFlag(true);
    }
  };

  return (
    <div style={{ position: 'relative' }}>
      {flag && (
        <Overlay onClose={() => setFlag(false)}>
          <CreatingLink />
        </Overlay>
      )}

      <div
        ref={buttonRef}
        className={`CrLinkNewButtonM ${isButtonDisabled ? "CrLinkNewButtonGrey" : ""}`} 
        onMouseEnter={handleMouseEnter}
        onMouseLeave={handleMouseLeave}
        onClick={handleClick} 
      >
        <div>
          <div className="CrLinkNewButtonText">Создать ссылку</div>
        </div>
      </div>

      {isPopupVisible && (
        <div
          ref={popupRef}
          className="custom-popup"
          onMouseLeave={handleMouseLeave}
          style={{ width: '250px' }}
        >
          <div className="custom-popup-content">
            <p>{popupMessage}</p>
            <button
              className="popup-button"
              onClick={() => navigate(PRICEPAGE_ROUTE)}
            >
              Обновиться до Pro
            </button>
          </div>
        </div>
      )}
    </div>
  );
};

export default CreateLinkNew;
