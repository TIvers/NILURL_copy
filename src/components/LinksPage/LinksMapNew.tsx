import React, { useRef, useState, useEffect } from "react";
import "../../styles/LinksPage/LinksMapNew.css";
import RedactingLink from "../creating-link/RedactingLink";
import QRComponent from "../qr-component/QRComponent";
import { Overlay } from "../../components";
import { GRAPHPAGE_ROUTE } from "../../LogicComp/utils/Const";
import { useNavigate } from "react-router-dom";

interface LinksMapInt {
  Data: string;
  SvgPath: string;
  pathS: string;
  pathL: string;
  UTM: boolean;
  Android: boolean;
  IOS: boolean;
  commentary: string;
  clicks: number;
  svgColor: string;
  backgrounds: string;
  tagValue: string;
  timer_flag: number;
  tag_flag: boolean;
  qr_open: boolean;
}

const LinksMapNew: React.FC<LinksMapInt> = ({
  Data,
  SvgPath,
  pathS,
  pathL,
  UTM,
  Android,
  IOS,
  commentary,
  clicks,
  svgColor,
  backgrounds,
  tagValue,
  timer_flag,
  tag_flag,
  qr_open,
}: LinksMapInt) => {
  const navigate = useNavigate();
  const [imageLoadError, setImageLoadError] = useState(false);
  const handleImageError = () => {
    setImageLoadError(true);
  };

  const [EditChangeFlag, setEditChangeFlag] = useState(false);
  const popupRef = useRef<HTMLDivElement>(null);
  const toggleEditPopup = () => {
    setEditChangeFlag(!EditChangeFlag);
  };

  const handleClickOutside = (event: MouseEvent) => {
    if (popupRef.current && !popupRef.current.contains(event.target as Node)) {
      setEditChangeFlag(false);
    }
  };

  const [linkChangeFlag, setLinkChangeFlag] = useState(false);
  const [qrFlag, setQrFlag] = useState(false);
  const ref = useRef<HTMLDivElement>(null);
  let count = 0;
  if (UTM) count++;
  if (Android) count++;
  if (IOS) count++;
  
  console.log("Количество активных флагов:", count);

  const closeCreatingLink = () => {
    setLinkChangeFlag(false);
  };
  const closeQrLink = () => {
    setQrFlag(false);
  };
  const [flagTimer] = useState(timer_flag);
  const [flagTag] = useState(tag_flag);
  const [isCommentPopupVisible, setIsCommentPopupVisible] = useState(false);
  const [gradient, setGradient] = useState("");
  const [copied, setCopied] = useState(false);

  const getRandomColor = () => {
    const letters = "0123456789ABCDEF";
    let color = "#";
    for (let i = 0; i < 6; i++) {
      color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
  };

  useEffect(() => {
    const applyRandomGradient = () => {
      const color1 = getRandomColor();
      const color2 = getRandomColor();
      const randomGradient = `linear-gradient(45deg, ${color1}, ${color2})`;
      setGradient(randomGradient);
    };
  
    applyRandomGradient(); 
  }, []);

  const onCopyClick = () => {
    console.log("Копирование текста:", pathS); 
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard
        .writeText(pathS)
        .then(() => {
          console.log("Текст успешно скопирован");
          setCopied(true);
          setTimeout(() => setCopied(false), 2000); 
        })
        .catch((err) => {
          console.error("Ошибка при копировании текста:", err);
        });
    } else {
      const textArea = document.createElement("textarea");
      textArea.value = pathS;
      document.body.appendChild(textArea);
      textArea.select();
      try {
        document.execCommand("copy");
        console.log("Текст скопирован через execCommand");
        setCopied(true);
        setTimeout(() => setCopied(false), 2000); 
      } catch (err) {
        console.error("Ошибка при копировании через execCommand:", err);
      }
      document.body.removeChild(textArea);
    }
  };

  useEffect(() => {
    if (EditChangeFlag) {
      document.addEventListener("mousedown", handleClickOutside);
    } else {
      document.removeEventListener("mousedown", handleClickOutside);
    }
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, [EditChangeFlag]);

  return (
    <div className="mainCLMP">
      {linkChangeFlag && (
        <Overlay onClose={closeCreatingLink}>
          <RedactingLink pathS={pathS} pathL={pathL} />
        </Overlay>
      )}
      {qrFlag && (
        <Overlay onClose={closeQrLink}>
          <QRComponent qr_open={qr_open} pathS={pathS} />
        </Overlay>
      )}
      {flagTimer === 1 && (
        <div className="timerCLMP">
          <svg
            width="17"
            height="16"
            viewBox="0 0 17 16"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            style={{ paddingTop: "2px" }}
          >
            <path
              d="M4.8453 8C5.11869 5.80761 6.98891 4.11111 9.25535 4.11111C11.71 4.11111 13.6998 6.10096 13.6998 8.55556C13.6998 11.0102 11.71 13 9.25535 13H6.47779M9.25557 8.55556V6.33333M8.14446 3H10.3667M3.70001 9.66667H6.47779M4.81112 11.3333H7.5889"
              stroke="black"
              stroke-width="1.06667"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </div>
      )}
      {flagTimer === 2 && (
        <div className="timerCLMPRed">
          <svg
            width="17"
            height="16"
            viewBox="0 0 17 16"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            style={{ paddingTop: "2px" }}
          >
            <path
              d="M4.8453 8C5.11869 5.80761 6.98891 4.11111 9.25535 4.11111C11.71 4.11111 13.6998 6.10096 13.6998 8.55556C13.6998 11.0102 11.71 13 9.25535 13H6.47779M9.25557 8.55556V6.33333M8.14446 3H10.3667M3.70001 9.66667H6.47779M4.81112 11.3333H7.5889"
              stroke="white"
              stroke-width="1.06667"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <line
              x1="0"
              y1="0"
              x2="17"
              y2="16"
              stroke="white"
              strokeWidth="1.06667"
              strokeLinecap="round"
            />
          </svg>
        </div>
      )}
      <div style={{ display: "inline-block" }}>
        <div className="SVGCOntLP">
          {!imageLoadError ? (
            <img
              className="SVGLinksLP"
              src={SvgPath}
              onError={handleImageError}
              alt="Привет"
            />
          ) : (
            <div
              className="gradient-circle"
              style={{
                background: gradient,
              }}
            ></div>
          )}
        </div>
      </div>
      <div className="LinksDateCopy">
        <div className="LinksDateTop">
          <div style={{ float: "left" }}>
          <div className="ShortLinkLPMP" onClick={onCopyClick}>
  {pathS}
</div>
            <div className="ShortLinkBtn">
              {/* <div
                className="blockForCopySVG"
                style={{ display: "flex", marginLeft: "10px" }}
              >
                {copied && (
                  <img
                    src={process.env.PUBLIC_URL + "/checkmark.png"}
                    style={{ width: "15px", height: "15px" }}
                  ></img>
                )}
                {!copied && (
                  <svg
                    onClick={() => {
                      onCopyClick();
                    }}
                    width="16"
                    height="16"
                    viewBox="0 0 13 12"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <path
                      d="M4.69974 8.9645H3.69974C3.14724 8.9645 2.69974 8.5085 2.69974 7.9465V2.518C2.69974 1.955 3.14724 1.5 3.69974 1.5H7.69974C8.25224 1.5 8.69974 1.9555 8.69974 2.518V3.451M5.69974 3.536H9.69974C10.2522 3.536 10.6997 3.991 10.6997 4.5535V9.982C10.6997 10.545 10.2522 11 9.69974 11H5.69974C5.14724 11 4.69974 10.5445 4.69974 9.982V4.5535C4.69974 3.9915 5.14724 3.536 5.69974 3.536Z"
                      stroke="#374151"
                      stroke-width="0.875"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                  </svg>
                )}
              </div> */}
              <div
                className="blockForCopySVG"
                style={{
                  display: commentary.trim() ? "flex" : "none",
                  marginLeft: "10px",
                  position: "relative",
                }}
                onMouseEnter={() =>
                  commentary.trim() && setIsCommentPopupVisible(true)
                }
                onMouseLeave={() =>
                  commentary.trim() && setIsCommentPopupVisible(false)
                }
              >
                <svg
                  height="16"
                  width="16"
                  viewBox="0 0 18 18"
                  xmlns="http://www.w3.org/2000/svg"
                  className="size-3.5"
                >
                  <g
                    fill="none"
                    stroke="#374151"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth="1.5"
                  >
                    <line x1="5.75" x2="9" y1="11.25" y2="11.25" />
                    <line x1="5.75" x2="12.25" y1="8.25" y2="8.25" />
                    <line x1="5.75" x2="12.25" y1="5.25" y2="5.25" />
                    <rect
                      height="14.5"
                      width="12.5"
                      rx="2"
                      ry="2"
                      x="2.75"
                      y="1.75"
                    />
                  </g>
                </svg>
                <rect
                  x="0.700012"
                  width="24"
                  height="24"
                  rx="12"
                  fill="#F3F4F6"
                />
                {isCommentPopupVisible && commentary.trim() && (
                  <div className="comment-popup">{commentary}</div>
                )}
              </div>
            </div>
          </div>
        </div>
        <div ref={ref} className="LinksDateBottom">
          <div style={{ marginRight: "10px", fontWeight: 500 }}>{pathL}</div>
          <div
            style={{
              fontWeight: 400,
              fontSize: "14px",
              color: "rgba(156, 163, 175, 1)",
            }}
          >
            {Data}
          </div>
          <div className="BlurAbsolute"></div>
        </div>
      </div>
      <div className="rightmainCLMP">
        {/* {count === 3 ? ( 
          <div className="threeIconsDevices">
            <ThreeIcons />
          </div>
        ) : (
          <div className="TwoIconsContainer">
            {count === 2 ? (
              <div className="TwoIconsD">
                <TwoIcons UTM={UTM} IOS={IOS} Android={Android} />
              </div>
            ) : (
              <div className="OneIconD" style={{ height: "26px" }}>
                <OneIcon UTM={UTM} IOS={IOS} Android={Android} />
              </div>
            )}
          </div>
        )} */}
        {flagTag && (
          <div
            className="tegCLMP"
            style={{
              backgroundColor: `${backgrounds}`,
              color: `${svgColor}`,
              border: `1px solid ${svgColor}`,
            }}
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
              className="lucide lucide-tag h-4 w-4"
              data-state="closed"
            >
              <path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z" />
              <circle cx="7.5" cy="7.5" r=".5" fill="currentColor" />
            </svg>
            <div className="tagValue">{tagValue}</div>
          </div>
        )}
        <div
          className="stats-button"
          onClick={() => navigate(GRAPHPAGE_ROUTE, { state: { pathS } })}
        >
          <button
            style={{
              float: "left",
              lineHeight: "24px",
              alignItems: "center",
              display: "flex",
            }}
          >
            <svg
              height="18"
              width="18"
              viewBox="0 0 18 18"
              xmlns="http://www.w3.org/2000/svg"
              className="text-gray-6000 h-4 w-4 shrink-0"
            >
              <g
                fill="none"
                stroke="#374151"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1.5"
              >
                <path d="M8.095,7.778l7.314,2.51c.222,.076,.226,.388,.007,.47l-3.279,1.233c-.067,.025-.121,.079-.146,.146l-1.233,3.279c-.083,.219-.394,.215-.47-.007l-2.51-7.314c-.068-.197,.121-.385,.318-.318Z" />
                <line x1="12.031" x2="16.243" y1="12.031" y2="16.243" />
                <line x1="7.75" x2="7.75" y1="1.75" y2="3.75" />
                <line x1="11.993" x2="10.578" y1="3.507" y2="4.922" />
                <line x1="3.507" x2="4.922" y1="11.993" y2="10.578" />
                <line x1="1.75" x2="3.75" y1="7.75" y2="7.75" />
                <line x1="3.507" x2="4.922" y1="3.507" y2="4.922" />
              </g>
            </svg>
          </button>
          <div className="CLicksTextANum">{clicks}</div>
        </div>
        <div onClick={toggleEditPopup} className="triDot">
          <svg
            width="20"
            height="20"
            viewBox="0 0 20 20"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M10.0003 10.8334C10.4606 10.8334 10.8337 10.4603 10.8337 10C10.8337 9.53978 10.4606 9.16669 10.0003 9.16669C9.54009 9.16669 9.16699 9.53978 9.16699 10C9.16699 10.4603 9.54009 10.8334 10.0003 10.8334Z"
              stroke="#6B7280"
              stroke-width="1.25"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              d="M10.0003 4.99998C10.4606 4.99998 10.8337 4.62688 10.8337 4.16665C10.8337 3.70641 10.4606 3.33331 10.0003 3.33331C9.54009 3.33331 9.16699 3.70641 9.16699 4.16665C9.16699 4.62688 9.54009 4.99998 10.0003 4.99998Z"
              stroke="#6B7280"
              stroke-width="1.25"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              d="M10.0003 16.6667C10.4606 16.6667 10.8337 16.2936 10.8337 15.8333C10.8337 15.3731 10.4606 15 10.0003 15C9.54009 15 9.16699 15.3731 9.16699 15.8333C9.16699 16.2936 9.54009 16.6667 10.0003 16.6667Z"
              stroke="#6B7280"
              stroke-width="1.25"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
          {EditChangeFlag && (
            <div
              ref={popupRef}
              className="edit-popup absolute z-[100] bg-white border border-gray-300 rounded-lg shadow-lg"
              style={{ right: "20px", top: "60px" }}
            >
              <div
                className="w-full sm:w-48 bg-white border border-[#e5e7eb] rounded-lg h-auto"
                onClick={(e) => e.stopPropagation()}
              >
                <div className="grid gap-px p-2">
                  <div
                    className="group flex w-full items-center justify-center gap-2 whitespace-nowrap rounded-md border text-sm transition-all border-transparent text-gray-500 duration-75 hover:bg-gray-100 h-9 px-2 font-medium"
                    onClick={() => {
                      setLinkChangeFlag(true);
                    }}
                  >
                    <svg
                      height="18"
                      width="18"
                      viewBox="0 0 18 18"
                      xmlns="http://www.w3.org/2000/svg"
                      className="h-4 w-4"
                    >
                      <g fill="currentColor">
                        <path
                          d="M2.75,15.25s3.599-.568,4.546-1.515,7.327-7.327,7.327-7.327c.837-.837,.837-2.194,0-3.03-.837-.837-2.194-.837-3.03,0,0,0-6.38,6.38-7.327,7.327-.947,.947-1.515,4.546-1.515,4.546h0Z"
                          fill="none"
                          stroke="currentColor"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="1.5"
                        />
                        <line
                          fill="none"
                          stroke="currentColor"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="1.5"
                          x1="9"
                          x2="15.25"
                          y1="15.25"
                          y2="15.25"
                        />
                      </g>
                    </svg>
                    <div className="min-w-0 truncate flex-1 text-left">
                      Редактировать
                    </div>
                  </div>

                  <div
                    className="group flex w-full items-center justify-center gap-2 whitespace-nowrap rounded-md border text-sm transition-all border-transparent text-gray-500 duration-75 hover:bg-gray-100 h-9 px-2 font-medium"
                    onClick={() => {
                      setQrFlag(true);
                    }}
                  >
                    <svg
                      height="18"
                      width="18"
                      viewBox="0 0 18 18"
                      xmlns="http://www.w3.org/2000/svg"
                      className="h-4 w-4"
                    >
                      <g fill="currentColor">
                        <rect
                          height="5"
                          width="5"
                          fill="none"
                          rx="1"
                          ry="1"
                          stroke="currentColor"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="1.5"
                          x="2.75"
                          y="2.75"
                        />
                        <rect
                          height="5"
                          width="5"
                          fill="none"
                          rx="1"
                          ry="1"
                          stroke="currentColor"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="1.5"
                          x="10.25"
                          y="2.75"
                        />
                        <rect
                          height="5"
                          width="5"
                          fill="none"
                          rx="1"
                          ry="1"
                          stroke="currentColor"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="1.5"
                          x="2.75"
                          y="10.25"
                        />
                        <rect
                          height="1.5"
                          width="1.5"
                          fill="currentColor"
                          stroke="none"
                          x="4.5"
                          y="4.5"
                        />
                        <rect
                          height="1.5"
                          width="1.5"
                          fill="currentColor"
                          stroke="none"
                          x="12"
                          y="4.5"
                        />
                        <rect
                          height="1.5"
                          width="1.5"
                          fill="currentColor"
                          stroke="none"
                          x="4.5"
                          y="12"
                        />
                        <rect
                          height="1.5"
                          width="1.5"
                          fill="currentColor"
                          stroke="none"
                          x="14.5"
                          y="14.5"
                        />
                        <rect
                          height="1.5"
                          width="1.5"
                          fill="currentColor"
                          stroke="none"
                          x="13"
                          y="13"
                        />
                        <rect
                          height="1.5"
                          width="1.5"
                          fill="currentColor"
                          stroke="none"
                          x="14.5"
                          y="11.5"
                        />
                        <rect
                          height="1.5"
                          width="2"
                          fill="currentColor"
                          stroke="none"
                          x="11"
                          y="14.5"
                        />
                        <rect
                          height="3"
                          width="1.5"
                          fill="currentColor"
                          stroke="none"
                          x="9.5"
                          y="11.5"
                        />
                        <rect
                          height="1.5"
                          width="3.5"
                          fill="currentColor"
                          stroke="none"
                          x="11"
                          y="10"
                        />
                      </g>
                    </svg>
                    <div className="min-w-0 truncate flex-1 text-left">
                      QR код
                    </div>
                  </div>

                  <div className="group flex w-full items-center justify-center gap-2 whitespace-nowrap rounded-md border text-sm transition-all border-transparent text-gray-500 duration-75 hover:bg-gray-100 h-9 px-2 font-medium" onClick={onCopyClick}>
                    {copied ? (
                      <img
                        src={process.env.PUBLIC_URL + "/checkmark.png"}
                        style={{ width: "15px", height: "15px" }}
                        alt="Скопировано"
                      />
                    ) : (
                      <svg
                        width="16"
                        height="16"
                        viewBox="0 0 13 12"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        style={{ cursor: "pointer" }}
                      >
                        <path
                          d="M4.69974 8.9645H3.69974C3.14724 8.9645 2.69974 8.5085 2.69974 7.9465V2.518C2.69974 1.955 3.14724 1.5 3.69974 1.5H7.69974C8.25224 1.5 8.69974 1.9555 8.69974 2.518V3.451M5.69974 3.536H9.69974C10.2522 3.536 10.6997 3.991 10.6997 4.5535V9.982C10.6997 10.545 10.2522 11 9.69974 11H5.69974C5.14724 11 4.69974 10.5445 4.69974 9.982V4.5535C4.69974 3.9915 5.14724 3.536 5.69974 3.536Z"
                          stroke="#374151"
                          strokeWidth="0.875"
                          strokeLinecap="round"
                          strokeLinejoin="round"
                        />
                      </svg>
                    )}
                    <div className="min-w-0 truncate flex-1 text-left">
                      Копировать
                    </div>
                  </div>
                </div>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default LinksMapNew;
