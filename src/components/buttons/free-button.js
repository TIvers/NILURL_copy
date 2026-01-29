import React from "react";
import "./buttons.css";

function FreeButton({ children, onClick, ...props }) {

  return (
    <button className="loader" id="free" onClick={onClick} style={{
      width: "100%",
      textAlign: "center",
    }}>
      <span>Попробовать</span>
    </button>
  );
}

export default FreeButton;
