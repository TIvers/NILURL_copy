import React, { useEffect, useMemo, useState } from "react";
import MapGP from "./MapGP";
import "../../styles/GraphPage/DeviceGP.css";
import { DateFromServInterface } from "../../LogicComp/GPFakeData";
import SortButtonDev from "../buttons/SortButtonDev";
import { Pie } from "react-chartjs-2";
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  ChartOptions,
} from "chart.js";
import ChartDataLabels from "chartjs-plugin-datalabels";
import { usePremium } from "../../LogicComp/DataProvider";

ChartJS.register(Title, Tooltip, Legend, ArcElement, ChartDataLabels);

interface AddresGpInt {
  Dates: DateFromServInterface[];
}

interface DualData {
  country: string;
  clicks: number;
}

const DevicesGp = ({ Dates }: AddresGpInt) => {
  const [data, setData] = useState<DualData[]>([]);
  const [Device, setDevice] = useState<DualData[]>([]);
  const [OC, setOC] = useState<DualData[]>([]);
  const [Browser, setBrowser] = useState<DualData[]>([]);
  const [currentIndex, setCurrentIndex] = useState(0);
  const [sortOption, setSortOption] = useState(0);
  const [flag, setFlag] = useState(false);
  const { isPremium } = usePremium();
  const [isPro] = useState(
    isPremium !== "free"
  );

  const categories = useMemo(() => [
    { name: "Устройство", data: Device },
    { name: "Браузер", data: Browser },
    { name: "ОС", data: OC },
  ], [Device, Browser, OC]);
  
  useEffect(() => {
    const device: DualData[] = [];
    const oc: DualData[] = [];
    const browser: DualData[] = [];

    Dates.forEach((value) => {
      let deviceFlag = false;
      device.forEach((d) => {
        if (d.country === value.device) {
          d.clicks++;
          deviceFlag = true;
        }
      });
      if (!deviceFlag) {
        device.push({ country: value.device, clicks: 1 });
      }

      let ocFlag = false;
      oc.forEach((o) => {
        if (o.country === value.os) {
          o.clicks++;
          ocFlag = true;
        }
      });
      if (!ocFlag) {
        oc.push({ country: value.os, clicks: 1 });
      }

      let browserFlag = false;
      browser.forEach((b) => {
        if (b.country === value.browser) {
          b.clicks++;
          browserFlag = true;
        }
      });
      if (!browserFlag) {
        browser.push({ country: value.browser, clicks: 1 });
      }
    });

    setDevice(device);
    setOC(oc);
    setBrowser(browser);
    setData(device);
  }, [Dates]);

  useEffect(() => {
    setData(categories[currentIndex].data);
  }, [currentIndex, categories]);

  useEffect(() => {
    let sortedData = [...categories[currentIndex].data];
    switch (sortOption) {
      case 0:
        sortedData = [...categories[currentIndex].data];
        break;
      case 1:
        sortedData.sort((a, b) => a.country.localeCompare(b.country));
        break;
      case 2:
        sortedData.sort((a, b) => b.country.localeCompare(a.country));
        break;
      case 3:
        sortedData.sort((a, b) => b.clicks - a.clicks);
        break;
      case 4:
        sortedData.sort((a, b) => a.clicks - b.clicks);
        break;
      default:
        break;
    }
    setData(sortedData);
  }, [currentIndex, categories, sortOption]);

  const columns = [
    { label: "Алфавит ↓", value: 1 },
    { label: "Алфавит ↑", value: 2 },
    { label: "По кликам ↓", value: 3 },
    { label: "По кликам ↑", value: 4 },
  ];

  const processPieData = (data: DualData[]) => {
    const totalClicks = data.reduce((sum, item) => sum + item.clicks, 0);
    const minDegree = 15;
    const minClicks = (minDegree / 360) * totalClicks;

    const maxOtherClicks = (30 / 360) * totalClicks;

    const sortedData = [...data].sort((a, b) => b.clicks - a.clicks);

    const topData = sortedData.filter((item) => item.clicks >= minClicks);
    const otherData = sortedData.filter((item) => item.clicks < minClicks);

    if (otherData.length > 0) {
      const otherClicks = otherData.reduce((sum, item) => sum + item.clicks, 0);
      const limitedOtherClicks = Math.min(otherClicks, maxOtherClicks);

      topData.push({ country: "Другое", clicks: limitedOtherClicks });

      if (otherClicks > maxOtherClicks) {
        const excessClicks = otherClicks - maxOtherClicks;
        const remainingSegments = topData.length - 1;

        topData.forEach((item, index) => {
          if (item.country !== "Другое") {
            item.clicks += excessClicks / remainingSegments;
          }
        });
      }
    }

    return topData;
  };

  
  const pieData = useMemo(() => ({
    labels: processPieData(data).map((d) => d.country),
    datasets: [
      {
        data: processPieData(data).map((d) => d.clicks),
        backgroundColor: [
          "#4285F4",
          "#DB4437",
          "#F4B400",
          "#0F9D58",
          "#AB47BC",
          "#00ACC1",
          "#FF5733",
          "#FFC300",
          "#DAF7A6",
          "#FF8C00",
          "#E67E22",
          "#2ECC71",
        ],
      },
    ],
  }), [data]);


  const options: ChartOptions<"pie"> = {
    plugins: {
      tooltip: {
        enabled: false, 
      },
      legend: {
        display: true,
        position: "bottom",
        labels: {
          boxWidth: 10,
          padding: 10,
          font: {
            size: 11,
            weight: "bold", 
            family: 'Arial, sans-serif', 
          },
          color: "#333",
        },
      },
      datalabels: {
        color: "#000",
        formatter: (value: number) => `${value}`,
        anchor: "center",
        align: "center",
        offset: 0,
        textAlign: "center",
        padding: {
          top: 0,
          bottom: 0,
        },
        font: {
          size: 12,
        },
      },
    },
    layout: {
      padding: {
        top: 0,
        bottom: 0,
        left: 0,
        right: 0,
      },
    },
    cutout: "40%",
    animation: {
      animateRotate: true,
      duration: 20,
      animateScale: false,
    },
    responsive: true,
    maintainAspectRatio: true,
  };

  const drawCustomLinesAndText = (chart: any) => {
    const ctx = chart.ctx;
    chart.data.datasets[0].data.forEach((value: number, i: number) => {
      const meta = chart.getDatasetMeta(0);
      const { startAngle, endAngle } = meta.data[i].getProps(
        ["startAngle", "endAngle"],
        true
      );
      const middleAngle = startAngle + (endAngle - startAngle) / 2;
      const radius = meta.data[i].getProps(["outerRadius"], true);

      const x = chart.width / 2 + (radius + 20) * Math.cos(middleAngle);
      const y = chart.height / 2 + (radius + 20) * Math.sin(middleAngle);

      ctx.beginPath();
      ctx.moveTo(
        chart.width / 2 + radius * Math.cos(middleAngle),
        chart.height / 2 + radius * Math.sin(middleAngle)
      );
      ctx.lineTo(x, y);
      ctx.stroke();

      ctx.textAlign = middleAngle < Math.PI ? "left" : "right";
      const label = chart.data.labels ? chart.data.labels[i] : "Unknown";
      ctx.fillText(`${label}: ${value}`, x, y - 10);
    });
  };

  useEffect(() => {
    const chart = ChartJS.getChart("pie-chart");
    if (chart) {
      if (chart.options.plugins) {
        (chart.options.plugins.tooltip as { enabled?: boolean }).enabled =
          false;
        (chart.options.plugins.legend as { display?: boolean }).display = true;
      }
      chart.render();
  
      chart.canvas.addEventListener("afterDraw", () => {
        drawCustomLinesAndText(chart);
      });
    }
  }, [pieData]);

  return (
    <div className="AddressCountryDev">
      <div className="AddHeader">
        <div className="CategoryDev">
          {categories.map((category, index) => (
            <span
              key={index}
              className={`CategoryItem ${
                currentIndex === index ? "selected" : ""
              }`}
              onClick={() => setCurrentIndex(index)}
            >
              {category.name}
            </span>
          ))}
        </div>
        <div className="FontSizeTextGPDev">
          <SortButtonDev columns={columns} setSortOption={setSortOption} />
          <button
            className={`ToggleViewButton ${flag ? "active" : ""}`}
            onClick={() => setFlag(!flag)}
          >
            <svg
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
              className={`ToggleViewIcon ${flag ? "active" : ""}`}
            >
              <path
                d="M21 10C21 6.13401 17.866 3 14 3V10H21Z"
                stroke={flag ? "#FFFFFF" : "#1C274C"}
                strokeWidth="1.8"
                strokeLinecap="round"
                strokeLinejoin="round"
              />
              <path
                d="M11 21C15.4183 21 19 17.4183 19 13H11V5C6.58172 5 3 8.58172 3 13C3 17.4183 6.58172 21 11 21Z"
                stroke={flag ? "#FFFFFF" : "#1C274C"}
                strokeWidth="1.8"
                strokeLinecap="round"
                strokeLinejoin="round"
              />
            </svg>
          </button>
        </div>
      </div>
      {isPro ? (
        <div
          style={{
            height: "300px",
            overflowY: "auto",
            overflowX: "hidden",
            marginTop: "25px",
            display: flag ? "flex" : "block",
            justifyContent: flag ? "center" : "initial",
            flexDirection: flag ? "column" : "initial",
            alignItems: flag ? "center" : "initial",
          }}
        >
          {flag ? (
            <Pie data={pieData} options={options} id="pie-chart" />
          ) : (
            data.map((value, index) => (
              <div key={index}>
                <MapGP
                  name={value.country}
                  clickCount={value.clicks}
                  SVG={"qwe"}
                  category={categories[currentIndex].name}
                  country_code={""}
                />
              </div>
            ))
          )}
        </div>
      ) : (
        <div className="blurred-container">
          <div className="blur-overlay"></div>
          <div className="access-icon">
          <svg
              version="1.1"
              id="Layer_1"
              x="0px"
              y="0px"
              viewBox="0 0 473.931 473.931"
              xmlSpace="preserve"
            >
              <circle
                style={{ fill: "#E84849" }}
                cx="236.966"
                cy="236.966"
                r="236.966"
              />
              <path
                style={{ fill: "#F4F5F5" }}
                d="M429.595,245.83c0,16.797-13.624,30.417-30.417,30.417H74.73c-16.797,0-30.421-13.62-30.421-30.417 v-17.743c0-16.797,13.624-30.417,30.421-30.417h324.448c16.793,0,30.417,13.62,30.417,30.417V245.83z"
              />
            </svg>
          </div>
          <div className="access-message">
            Для получения доступа к этому функционалу обновите тарифный план
          </div>
        </div>
      )}
    </div>
  );
};

export default DevicesGp;
